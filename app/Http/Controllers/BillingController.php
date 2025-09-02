<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billings;
use App\Models\Clients;
use App\Models\Payments;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billings::with('client')
        ->orderBy('billing_date', 'desc')
        ->paginate(25);
        $clients = Clients::all();
        return view('admin.billings', compact('billings', 'clients'));
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'client_id'         => 'required|exists:clients,id',
                'billing_id'        => 'required|string|max:255|unique:billings,billing_id',
                'billing_date'      => 'required|date',
                'due_date'          => 'required|date',
                'reading_date'      => 'required|date',
                'previous_reading'  => 'required|numeric|min:0',
                'present_reading'   => 'required|numeric|min:0',
                'current_bill'      => 'required|numeric|min:0',
                'maintenance_cost'  => 'required|numeric|min:0',
                'installation_fee'  => 'required|numeric|min:0',
            ]);

            // Compute consumed
            $rawConsumed = max(0, $validated['present_reading'] - $validated['previous_reading']);
            $remaining = $rawConsumed;
            $consumed = 0;

            if ($remaining > 0) {
                // First 0–10 cu.m.
                $consumed += 150;
                $remaining -= 10;

                if ($remaining > 0) {
                    // Next 11–20 (10 cu.m. max)
                    $cu = min(10, $remaining);
                    $consumed += $cu * 16;
                    $remaining -= $cu;
                }

                if ($remaining > 0) {
                    // Next 21–30 (10 cu.m.)
                    $cu = min(10, $remaining);
                    $consumed += $cu * 19;
                    $remaining -= $cu;
                }

                if ($remaining > 0) {
                    // Next 31–40
                    $cu = min(10, $remaining);
                    $consumed += $cu * 23;
                    $remaining -= $cu;
                }

                if ($remaining > 0) {
                    // Next 41–50
                    $cu = min(10, $remaining);
                    $consumed += $cu * 26;
                    $remaining -= $cu;
                }

                if ($remaining > 0) {
                    // Above 50
                    $consumed += $remaining * 30;
                }
            } else {
                // No consumption, still apply ₱150
                $consumed = 150;
            }


            $currentBill = $validated['current_bill'];
            $billingDate = Carbon::parse($validated['billing_date']);
            $arrears = 0;
            $penalty = 0;

            // Fetch unpaid or partial past payments before billing_date
            $pastPayments = Payments::where('client_id', $validated['client_id'])
                ->where('billing_month', '<', $billingDate)
                ->get();

            foreach ($pastPayments as $payment) {
                $unpaidAmount = 0;
                if ($payment->status === 'unpaid') {
                    $unpaidAmount = $payment->current_bill;
                } elseif ($payment->status === 'partial') {
                    $paid = $payment->partial_payment_amount ?? 0;
                    $unpaidAmount = max(0, $payment->current_bill - $paid);
                }

                if ($unpaidAmount > 0) {
                    $arrears += $unpaidAmount;

                    $dueDate = Carbon::parse($payment->billing_month)->addDays(14);
                    if ($billingDate->gt($dueDate)) {
                        $daysLate = $dueDate->diffInDays($billingDate);
                        $partialPenalty = round($unpaidAmount * 0.005 * $daysLate, 2);
                        if ($partialPenalty > 0) {
                            $penalty += $partialPenalty;
                        }
                    }
                }
            }

            // ✅ Compute total amount including consumed
            $totalAmount = $currentBill
                + $arrears
                + $penalty
                + $validated['maintenance_cost']
                + $validated['installation_fee']
                + $consumed;

            // Create billing
            $billing = Billings::create([
                ...$validated,
                'total_penalty' => $penalty,
                'total_amount'  => $totalAmount,
                'current_bill'  => $currentBill,
                'consumed'      => $consumed,
                'due_date'      => $validated['due_date'],
                'reading_date'  => $validated['reading_date'],
            ]);

            // Create payment record
            Payments::create([
                'client_id'     => $billing->client_id,
                'billing_month' => $billing->billing_date,
                'current_bill'  => $currentBill,
                'arrears'       => $arrears,
                'penalty'       => $penalty,
                'total_amount'  => $totalAmount,
                'partial_payment_amount' => 0,
                'payment_type'  => 'N/A',
                'status'        => 'unpaid',
            ]);

            return redirect()->route('admin.billings.index')
                ->with('success', 'Billing and payment record created successfully.');
        } catch (\Exception $e) {
            Log::error('Billing Store Error: ' . $e->getMessage());
            return redirect()->route('admin.billings.index')
                ->with('error', 'Something went wrong while saving billing.');
        }
    }

    public function show(string $id)
    {
        $billing = Billings::with('client')->findOrFail($id);
        return view('admin.billings.show', compact('billing'));
    }

    public function destroy(string $id)
    {
        try {
            $billing = Billings::findOrFail($id);
            $billing->delete();
            return redirect()->route('admin.billings.index')->with('success', 'Billing deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.billings.index')->with('error', 'Failed to delete billing: ' . $e->getMessage());
        }
    }

    public function nextId()
    {
        $lastBilling = Billings::orderBy('billing_id', 'desc')->first();
        $nextId = $lastBilling ? ((int)$lastBilling->billing_id + 1) : 1;
        return response()->json(['next_billing_id' => $nextId]);
    }

    public function print($id)
    {
        $billing = Billings::with('client')->findOrFail($id);

        $pastUnpaidPayments = Payments::where('client_id', $billing->client_id)
            ->where('status', 'unpaid')
            ->where('billing_month', '<', $billing->billing_date)
            ->get()
            ->toArray();

        $arrears = array_sum(array_column($pastUnpaidPayments, 'current_bill'));

        $arrearsBreakdown = array_map(function ($payment) {
            return [
                'billing_month' => $payment['billing_month'],
                'current_bill' => floatval($payment['current_bill']),
            ];
        }, $pastUnpaidPayments);

        $penalty = 0;
        $penaltyBreakdown = [];

        foreach ($pastUnpaidPayments as $unpaid) {
            $billingMonth = $unpaid['billing_month'];
            $dueDate = Carbon::parse($billingMonth)->addDays(14);
            $now = Carbon::now();

            if ($now->gt($dueDate)) {
                $daysLate = $dueDate->diffInDays($now);
                $partialPenalty = round((float)$unpaid['current_bill'] * 0.005 * $daysLate, 2);

                if ($partialPenalty > 0) {
                    $penalty += $partialPenalty;
                    $penaltyBreakdown[] = [
                        'billing_month'   => $billingMonth,
                        'due_date'        => $dueDate->format('Y-m-d'),
                        'days_late'       => $daysLate,
                        'current_bill'    => (float)$unpaid['current_bill'],
                        'partial_penalty' => $partialPenalty,
                    ];
                }
            }
        }

        return view('admin.print', compact(
            'billing', 'arrears', 'penalty', 'arrearsBreakdown', 'penaltyBreakdown'
        ));
    }

    public function getPenalty($clientId)
    {
        $billingDate = request()->query('billing_date', now());
        $billingDateCarbon = Carbon::parse($billingDate);
        $penalty = 0;

        $pastPayments = Payments::where('client_id', $clientId)
            ->where('billing_month', '<', $billingDate)
            ->get();

        foreach ($pastPayments as $payment) {
            $unpaidAmount = 0;

            if ($payment->status === 'unpaid') {
                $unpaidAmount = $payment->current_bill;
            } elseif ($payment->status === 'partial') {
                $paid = $payment->partial_payment_amount ?? 0;
                $unpaidAmount = max(0, $payment->current_bill - $paid);
            }

            if ($unpaidAmount > 0) {
                $dueDate = Carbon::parse($payment->billing_month)->addDays(14);
                $daysLate = $dueDate->diffInDays($billingDateCarbon, false);

                if ($daysLate > 0) {
                    $partialPenalty = round($unpaidAmount * 0.005 * $daysLate, 2);
                    if ($partialPenalty > 0) {
                        $penalty += $partialPenalty;
                    }
                }
            }
        }

        return response()->json(['penalty' => number_format($penalty, 2, '.', '')]);
    }

    public function getClientArrears($clientId)
    {
        $billingDate = request()->query('billing_date', now());
        $arrears = 0;

        $pastPayments = Payments::where('client_id', $clientId)
            ->where('billing_month', '<', $billingDate)
            ->get();

        foreach ($pastPayments as $payment) {
            $unpaidAmount = 0;

            if ($payment->status === 'unpaid') {
                $unpaidAmount = $payment->current_bill;
            } elseif ($payment->status === 'partial') {
                $paid = $payment->partial_payment_amount ?? 0;
                $unpaidAmount = max(0, $payment->current_bill - $paid);
            }

            if ($unpaidAmount > 0) {
                $arrears += $unpaidAmount;
            }
        }

        return response()->json(['arrears' => number_format($arrears, 2, '.', '')]);
    }
}
