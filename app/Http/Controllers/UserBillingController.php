<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\UserBilling;
use App\Models\Clients;
use App\Models\Payments;
use App\Models\ActivityLog;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use App\Models\ProblemReport;
use App\Models\Users;
use Barryvdh\DomPDF\Facade\Pdf;

class UserBillingController extends Controller
{
    /* ------------------------------------------------------------------
     |  Helper – pick the key that belongs to current mode (test|live)
     * ------------------------------------------------------------------ */
    private function secretKey(): string
    {
        $mode = config('paymongo.mode'); // 'test' or 'live'
        return config("paymongo.{$mode}_secret"); // test_secret | live_secret
    }

    /* ==================================================================
       ORIGINAL METHODS – ONLY the Http:: calls changed
       ================================================================== */

    /**
     * Automatically create a billing for a user (called from Admin BillingController).
     */
    public static function createUserBilling(
        int $clientId,
        string $billingId,
        string $billingDate,
        string $dueDate,
        float $amount,
        float $currentBill = 0,
        float $arrears = 0,
        float $penalty = 0,
        float $consumed = 0
    ): void {
        try {
            $user = Clients::find($clientId)?->user;
            if ($user) {
                UserBilling::create([
                    'user_id'      => $user->id,
                    'bill_number'  => $billingId,
                    'billing_date' => $billingDate,
                    'due_date'     => $dueDate,
                    'amount_due'   => $amount,
                    'current_bill' => $currentBill,
                    'arrears'      => $arrears,
                    'penalty'      => $penalty,
                    'consumed'     => $consumed,
                    'status'       => 'unpaid',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create user billing: ' . $e->getMessage());
        }
    }

    /**
     * Create a GCash payment source via PayMongo and show QR code.
     */
    public function payWithGCash(int $id)
    {
        $bill = UserBilling::findOrFail($id);

        try {
            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
            ])
                ->withBasicAuth($this->secretKey(), '')   // MODE-AWARE
                ->post(config('paymongo.base_url') . '/v1/sources', [  // CONFIG URL
                    'data' => [
                        'attributes' => [
                            'amount'   => (int)($bill->amount_due * 100),
                            'currency' => 'PHP',
                            'type'     => 'gcash',
                            'redirect' => [
                                'success' => route('user.billing.success', ['id' => $bill->id]),
                                'failed'  => route('user.billing.failed', ['id' => $bill->id]),
                            ],
                            'billing' => [
                                'name'  => Auth::user()->name ?? 'Guest',
                                'email' => Auth::user()->email ?? 'noemail@example.com',
                            ],
                        ],
                    ],
                ]);

            $json = $response->json();

            if (isset($json['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => $json['errors'][0]['detail'] ?? 'An error occurred.'
                ], 400);
            }

            $checkoutUrl = $json['data']['attributes']['redirect']['checkout_url'] ?? null;

            if ($checkoutUrl) {
                $renderer = new ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                    new SvgImageBackEnd()
                );
                $writer  = new Writer($renderer);
                $qrCode  = base64_encode($writer->writeString($checkoutUrl));

                return response()->json([
                    'success'     => true,
                    'checkoutUrl' => $checkoutUrl,
                    'qrCode'      => $qrCode,
                    'title'       => 'Pay Bill #' . $bill->bill_number,
                    'amount'      => $bill->amount_due,
                    'billId'      => $bill->id
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create GCash payment link.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle PayMongo webhook for payment confirmation.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        if (!empty($payload['data']['attributes']['paid_at'])) {
            $reference = $payload['data']['attributes']['reference_number'] ?? null;
            $bill = UserBilling::where('bill_number', $reference)->first();

            if ($bill) {
                $bill->status         = 'paid';
                $bill->payment_method = 'GCash';
                $bill->payment_date   = now();
                $bill->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function paymentSuccess(int $id)
    {
        $userBilling = UserBilling::findOrFail($id);
        $userBilling->update([
            'status'         => 'paid',
            'payment_method' => 'GCash',
            'payment_date'   => now(),
        ]);

        $payment = Payments::where('user_billing_id', $id)->first();

        if ($payment) {
            $payment->update([
                'status'                 => 'paid via gcash',
                'payment_type'           => 'gcash',
                'partial_payment_amount' => $payment->total_amount,
            ]);

            ActivityLog::create([
                'user_id'  => Auth::id(),
                'activity' => 'Payment Received',
                'details'  => 'User paid bill #' . $userBilling->bill_number . ' via GCash.',
            ]);

            return redirect()->route('user.billing')
                ->with('success', 'Payment successful via GCash!')
                ->with('pdfUrl', route('user.receipt.download', $payment->id));
        }

        return redirect()->route('user.billing')->with('error', 'Payment not completed.');
    }

    /**
     * Payment failed callback.
     */
    public function paymentFailed(int $id)
    {
        return redirect()->route('user.billing')->with('error', 'GCash payment was not completed.');
    }

    /**
     * Arrears – create source.
     */
    public function payArrearsOnly($id)
    {
        $userBilling = UserBilling::findOrFail($id);
        $arrears = $userBilling->arrears;

        if ($arrears <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No arrears to pay for this bill.'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'content-type' => 'application/json',
            ])
                ->withBasicAuth($this->secretKey(), '')   // MODE-AWARE
                ->post(config('paymongo.base_url') . '/v1/sources', [  // CONFIG URL
                    'data' => [
                        'attributes' => [
                            'amount'   => (int)($arrears * 100),
                            'currency' => 'PHP',
                            'type'     => 'gcash',
                            'redirect' => [
                                'success' => route('user.billing.arrears.success', ['id' => $id]),
                                'failed'  => route('user.billing.arrears.failed', ['id' => $id]),
                            ],
                        ],
                    ],
                ]);

            $json = $response->json();

            if (isset($json['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => $json['errors'][0]['detail'] ?? 'An error occurred.'
                ], 400);
            }

            $checkoutUrl = $json['data']['attributes']['redirect']['checkout_url'] ?? null;

            if ($checkoutUrl) {
                $renderer = new ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
                    new SvgImageBackEnd()
                );
                $writer  = new Writer($renderer);
                $qrCode  = base64_encode($writer->writeString($checkoutUrl));

                return response()->json([
                    'success'     => true,
                    'checkoutUrl' => $checkoutUrl,
                    'qrCode'      => $qrCode,
                    'title'       => 'Pay Arrears for Bill #' . $userBilling->bill_number,
                    'amount'      => $arrears,
                    'billId'      => $userBilling->id
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create GCash payment link.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function arrearsSuccess(int $id)
    {
        $userBilling = UserBilling::findOrFail($id);
        $payment = Payments::where('user_billing_id', $id)->first();

        if ($payment) {
            $payment->update([
                'status'                 => 'partial',
                'payment_type'           => 'gcash (arrears only)',
                'partial_payment_amount' => $userBilling->arrears,
            ]);

            $userBilling->update(['status' => 'Partially Paid']);

            ActivityLog::create([
                'user_id'  => Auth::id(),
                'activity' => 'Arrears Payment Received',
                'details'  => 'User partially paid bill #' . $userBilling->bill_number . ' via GCash.',
            ]);

            return redirect()->route('user.billing')
                ->with('success', 'Arrears payment successful via GCash!')
                ->with('pdfUrl', route('user.receipt.download', $payment->id));
        }

        return redirect()->route('user.billing')->with('error', 'Arrears payment was not completed.');
    }

    public function arrearsFailed(int $id)
    {
        return redirect()->route('user.billing')->with('error', 'Arrears payment was not completed.');
    }

    public function billingPage()
    {
        $userId = Auth::id();

        $billings = UserBilling::where('user_id', $userId)
            ->orderBy('billing_date', 'desc')
            ->paginate(10);

        $client = Auth::user()->client;
        $userReports = $client
            ? ProblemReport::where('client_id', $client->id)->orderBy('created_at', 'desc')->get()
            : collect();

        return view('user.billing', compact('billings', 'userReports'));
    }

    public function print($billingId)
    {
        $billing = UserBilling::with('user')->findOrFail($billingId);

        $unpaidBills = UserBilling::where('user_id', $billing->user_id)
            ->where('id', '<', $billing->id)
            ->where('status', 'Unpaid')
            ->get();

        $arrears = $unpaidBills->sum('current_bill');
        $penalty = $unpaidBills->sum(function ($b) {
            $daysLate = now()->diffInDays(\Carbon\Carbon::parse($b->due_date));
            return $daysLate * 0.005;
        });

        $arrearsBreakdown = $unpaidBills->map(function ($b) {
            return [
                'billing_month' => $b->billing_month,
                'current_bill'  => $b->current_bill,
            ];
        })->toArray();

        $penaltyBreakdown = $unpaidBills->map(function ($b) {
            $daysLate = now()->diffInDays(\Carbon\Carbon::parse($b->due_date));
            return [
                'billing_month'   => $b->billing_month,
                'due_date'        => $b->due_date,
                'days_late'       => $daysLate,
                'partial_penalty' => $daysLate * 0.005,
            ];
        })->toArray();

        return view('user.billing_print', compact(
            'billing',
            'arrears',
            'penalty',
            'arrearsBreakdown',
            'penaltyBreakdown'
        ));
    }

    public function generateReceipt(int $paymentId)
    {
        $payment = Payments::with('userBilling.user')->findOrFail($paymentId);
        $billing = $payment->userBilling;

        if (!$billing) {
            return redirect()->route('user.billing')->with('error', 'Billing not found for this payment.');
        }

        return view('user.user_receipt', [
            'payment' => $payment,
            'billing' => $billing,
            'user'    => $billing->user,
            'date'    => now()->format('F d, Y'),
        ]);
    }

    public function downloadReceipt(int $paymentId)
    {
        $payment = Payments::with('userBilling.user')->findOrFail($paymentId);
        $billing = $payment->userBilling;

        $pdf = PDF::loadView('user.user_receipt', [
            'payment' => $payment,
            'billing' => $billing,
            'user'    => $billing->user,
            'date'    => now()->format('F d, Y'),
        ]);

        return $pdf->download('Receipt_' . $payment->id . '.pdf');
    }
}