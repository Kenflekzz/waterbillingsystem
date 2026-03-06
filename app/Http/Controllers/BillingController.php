<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billings;
use App\Models\Clients;
use App\Models\Payments;
use Carbon\Carbon;
use App\Models\UserBilling;
use App\Models\Notification;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billings::with('client')
            ->join('clients', 'billings.client_id', '=', 'clients.id')
            ->orderBy('clients.full_name', 'asc')
            ->orderBy('billing_date', 'desc')
            ->select('billings.*')
            ->paginate(25);

        $clients = Clients::all();

        return view('admin.billings', compact('billings', 'clients'));
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'client_id'         => 'required|exists:clients,id',
        'billing_date'      => 'required|date',
        'due_date'          => 'required|date|after_or_equal:billing_date',
        'reading_date'      => 'required|date',
        'previous_reading'  => 'required|numeric|min:0',
        'present_reading'   => 'required|numeric|min:0|gte:previous_reading',
        'current_bill'      => 'required|numeric|min:0',
        'maintenance_cost'  => 'nullable|numeric|min:0',
        'installation_fee'  => 'nullable|numeric|min:0',
    ]);

    $validated['maintenance_cost'] = $validated['maintenance_cost'] ?? 0;
    $validated['installation_fee'] = $validated['installation_fee'] ?? 0;

    // ✅ CHECK 1 — Billing date must not be in the past month
    $billingDateCarbon = Carbon::parse($validated['billing_date']);
    $currentMonth = Carbon::now()->startOfMonth();

    if ($billingDateCarbon->lt($currentMonth)) {
        return redirect()->back()
            ->withErrors(['billing_date' => 'Cannot issue a billing for ' . $billingDateCarbon->format('F Y') . '. Billing date must not be in a past month.'])
            ->withInput()
            ->with('success', null);
    }

    // ✅ CHECK 2 — Billing date must be after the client's last billing date
    $lastBilling = Billings::where('client_id', $validated['client_id'])
        ->orderBy('billing_date', 'desc')
        ->first();

    if ($lastBilling && $billingDateCarbon->lte(Carbon::parse($lastBilling->billing_date))) {
        return redirect()->back()
            ->withErrors(['billing_date' => 'Cannot issue a billing for ' . $billingDateCarbon->format('F Y') . '. The latest billing for this client is already ' . Carbon::parse($lastBilling->billing_date)->format('F Y') . '. Billing must move forward.'])
            ->withInput()
            ->with('success', null);
    }

    // 1️⃣ Calculate water charge (WEIRD LOGIC - FIXED)
    $cubicMetres = $validated['present_reading'] - $validated['previous_reading'];
    
    if ($cubicMetres <= 10) {
        $waterCharge = 150;
    } else {
        $subtractedValue = $cubicMetres - 10;
        $rate = 0;

        if ($subtractedValue <= 10) {
            $rate = 16;
        } elseif ($subtractedValue <= 20) {
            $rate = 19;
        } elseif ($subtractedValue <= 30) {
            $rate = 23;
        } elseif ($subtractedValue <= 50) {  // ← FIXED: Changed from 40 to 50
            $rate = 26;
        } else {
            $rate = 30;
        }

        $waterCharge = 150 + ($subtractedValue * $rate);
    }

    // 2️⃣ Calculate arrears and penalty with breakdown
    $billingDate = Carbon::parse($validated['billing_date']);
    $arrears = 0;
    $penalty = 0;
    $arrearsBreakdown = [];
    $penaltyBreakdown = [];

    // Get all previous unpaid/partial payments for arrears breakdown
    $previousPayments = Payments::where('client_id', $validated['client_id'])
        ->where('billing_month', '<', $billingDate)
        ->whereIn('status', ['unpaid', 'partial'])
        ->orderBy('billing_month', 'asc')
        ->get();

    foreach ($previousPayments as $payment) {
        $status = strtolower($payment->status);
        $ptype = strtolower($payment->payment_type ?? '');
        $currentBill = floatval($payment->current_bill);
        $paidAmount = floatval($payment->partial_payment_amount ?? 0);
        $prevArrears = floatval($payment->arrears ?? 0);
        $paymentArrears = 0;

        // Calculate arrears for this payment
        if ($status === 'paid' || $ptype === 'full' || $ptype === 'gcash') {
            $paymentArrears = 0;
        } elseif ($status === 'partial') {
            $paymentArrears = max(0, ($currentBill + $prevArrears) - $paidAmount);
        } elseif ($status === 'unpaid') {
            $paymentArrears = $currentBill + $prevArrears;
        }

        if ($paymentArrears > 0) {
            $arrears += $paymentArrears;
            
            // Add to arrears breakdown
            $arrearsBreakdown[] = [
                'billing_month' => $payment->billing_month,
                'current_bill' => $paymentArrears,
            ];

            // Calculate penalty for this arrears
            $dueDate = Carbon::parse($payment->billing_month)->addDays(14);
            $daysLate = $dueDate->diffInDays($billingDate, false);
            
            if ($daysLate > 0) {
                $partialPenalty = round($paymentArrears * 0.005 * $daysLate, 2);
                $penalty += $partialPenalty;
                
                // Add to penalty breakdown
                $penaltyBreakdown[] = [
                    'billing_month' => $payment->billing_month,
                    'due_date' => $dueDate,
                    'days_late' => $daysLate,
                    'partial_penalty' => $partialPenalty,
                    'arrears_amount' => $paymentArrears,
                ];
            }
        }
    }

    // 3️⃣ Final bill
    $totalAmount = $waterCharge + $arrears + $penalty + $validated['maintenance_cost'] + $validated['installation_fee'];

    // 4️⃣ Generate per-client billing_id
    $lastClientBilling = Billings::where('client_id', $validated['client_id'])
        ->orderBy('billing_id', 'desc')
        ->first();
    $billingId = $lastClientBilling ? ($lastClientBilling->billing_id + 1) : 1;

    // 5️⃣ Save billing
    $billing = Billings::create([
        ...$validated,
        'billing_id'    => $billingId,
        'total_penalty' => $penalty,
        'total_amount'  => $totalAmount,
        'current_bill'  => $waterCharge,
        'consumed'      => $cubicMetres,
    ]);

    // 6️⃣ ALWAYS create payment record
    $payment = Payments::create([
        'client_id'              => $validated['client_id'],
        'billing_month'          => $billing->billing_date,
        'current_bill'           => $waterCharge,
        'arrears'                => $arrears,
        'penalty'                => $penalty,
        'total_amount'           => $totalAmount,
        'partial_payment_amount' => 0,
        'payment_type'           => 'N/A',
        'status'                 => 'unpaid',
    ]);

    // 7️⃣ Create UserBilling and Notification ONLY if client has user_id
    $client = Clients::find($validated['client_id']);
    
    if ($client && $client->user_id) {
        $userBilling = UserBilling::create([
            'user_id'       => $client->user_id,
            'bill_number'   => "C{$client->id}-BILL-{$billingId}",
            'billing_date'  => $billing->billing_date,
            'due_date'      => $billing->due_date,
            'amount_due'    => $totalAmount,
            'current_bill'  => $waterCharge,
            'arrears'       => $arrears,
            'penalty'       => $penalty,
            'consumed'      => $cubicMetres,
            'status'        => 'unpaid',
        ]);

        $payment->update(['user_billing_id' => $userBilling->id]);

        Notification::create([
            'user_id'     => $client->user_id,
            'type'        => 'billing',
            'related_id'  => $billing->id,
            'title'       => "New Billing Issued: #{$billingId}",
            'message'     => "A new billing statement (Bill #{$billingId}) has been issued to your account."
        ]);

        UserBilling::where('user_id', $client->user_id)->update(['updated_at' => now()]);
    }

    // 8️⃣ Return with breakdown data for printing
    return redirect()->route('admin.billings.index')
        ->with('success', 'Billing and payment record created successfully.')
        ->with('print_data', [
            'billing' => $billing,
            'arrears' => $arrears,
            'penalty' => $penalty,
            'arrearsBreakdown' => $arrearsBreakdown,
            'penaltyBreakdown' => $penaltyBreakdown,
            'totalAmount' => $totalAmount,
        ]);
}

    public function show(string $id)
    {
        $billing = Billings::with('client')->findOrFail($id);
        return view('admin.billings.show', compact('billing'));
    }

    public function destroy(string $id)
    {
        $billing = Billings::findOrFail($id);
        $billing->delete();

        return redirect()->route('admin.billings.index')->with('success', 'Billing deleted successfully.');
    }

    public function nextId()
    {
        $lastBilling = Billings::orderBy('billing_id', 'desc')->first();
        $nextId = $lastBilling ? ((int) $lastBilling->billing_id + 1) : 1;

        return response()->json(['next_billing_id' => $nextId]);
    }

    public function getPenalty($clientId)
    {
        $billingDate = request()->query('billing_date', now());
        $billingDateCarbon = Carbon::parse($billingDate);

        $history = Payments::where('client_id', $clientId)
            ->where('billing_month', '<', $billingDateCarbon)
            ->orderBy('billing_month', 'asc')
            ->get();

        $penalty = 0;

        foreach ($history as $bill) {
            if (in_array($bill->payment_type, ['full', 'gcash']) && $bill->status === 'paid') {
                continue;
            }

            $unpaid = 0;
            if ($bill->status === 'unpaid') {
                $unpaid = floatval($bill->current_bill);
            } elseif ($bill->status === 'partial') {
                $unpaid = max(0, floatval($bill->current_bill) - floatval($bill->partial_payment_amount ?? 0));
            }

            if ($unpaid <= 0) continue;

            $laterFull = Payments::where('client_id', $clientId)
                ->where('billing_month', '>', $bill->billing_month)
                ->where('billing_month', '<', $billingDateCarbon)
                ->where('payment_type', 'full')
                ->where('status', 'paid')
                ->exists();

            if ($laterFull) continue;

            $dueDate = Carbon::parse($bill->billing_month)->addDays(14);
            $daysLate = $dueDate->diffInDays($billingDateCarbon, false);
            if ($daysLate > 0) {
                $penalty += round($unpaid * 0.005 * $daysLate, 2);
            }
        }

        return response()->json(['penalty' => number_format($penalty, 2, '.', '')]);
    }

    public function getClientArrears($clientId)
    {
        $billingDate = request()->query('billing_date', now());
        $billingDateCarbon = Carbon::parse($billingDate);
        $arrears = 0;

        $previousPayments = Payments::where('client_id', $clientId)
            ->where('billing_month', '<', $billingDateCarbon)
            ->orderBy('billing_month', 'asc')
            ->get();

        foreach ($previousPayments as $payment) {
            $ptype = strtolower($payment->payment_type ?? '');
            $paidAmount = floatval($payment->partial_payment_amount ?? 0);
            $currentBill = floatval($payment->current_bill ?? 0);

            if ($payment->status === 'paid' || $ptype === 'full' || $ptype === 'gcash') {
                continue;
            }

            $remainingUnpaid = ($payment->status === 'partial') ? max(0, $currentBill - $paidAmount) : $currentBill;
            $arrears += $remainingUnpaid;
        }

        return response()->json(['arrears' => round($arrears, 2)]);
    }

   public function print($id)
{
    $billing = Billings::with('client')->findOrFail($id);
    
    $billingDate = Carbon::parse($billing->billing_date);
    $arrears = 0;
    $penalty = 0;
    $arrearsBreakdown = [];
    $penaltyBreakdown = [];
    
    // Get previous unpaid/partial payments
    $previousPayments = Payments::where('client_id', $billing->client_id)
        ->where('billing_month', '<', $billingDate)
        ->whereIn('status', ['unpaid', 'partial'])
        ->orderBy('billing_month', 'asc')
        ->get();
    
    foreach ($previousPayments as $payment) {
        $status = strtolower($payment->status);
        $currentBill = floatval($payment->current_bill);
        $paidAmount = floatval($payment->partial_payment_amount ?? 0);
        $prevArrears = floatval($payment->arrears ?? 0);
        $paymentArrears = 0;
        
        if ($status === 'partial') {
            $paymentArrears = max(0, ($currentBill + $prevArrears) - $paidAmount);
        } elseif ($status === 'unpaid') {
            $paymentArrears = $currentBill + $prevArrears;
        }
        
        if ($paymentArrears > 0) {
            $arrears += $paymentArrears;
            
            $arrearsBreakdown[] = [
                'billing_month' => $payment->billing_month,
                'current_bill' => $paymentArrears,
            ];
            
            $dueDate = Carbon::parse($payment->billing_month)->addDays(14);
            $daysLate = $dueDate->diffInDays($billingDate, false);
            
            if ($daysLate > 0) {
                $partialPenalty = round($paymentArrears * 0.005 * $daysLate, 2);
                $penalty += $partialPenalty;
                
                $penaltyBreakdown[] = [
                    'billing_month' => $payment->billing_month,
                    'due_date' => $dueDate->toDateString(),
                    'days_late' => $daysLate,
                    'partial_penalty' => $partialPenalty,
                    'arrears_amount' => $paymentArrears,
                ];
            }
        }
    }
    
    // IMPORTANT: Use calculated values, not stored values
    // The stored values in $billing->arrears and $billing->penalty are from when bill was created
    // We recalculate here to show current arrears/penalty in the print view
    
    $totalAmount = $billing->current_bill + $arrears + $penalty + $billing->maintenance_cost + $billing->installation_fee;
    
    return view('admin.print', compact(
        'billing',
        'arrears',           // Calculated: 10890
        'penalty',           // Calculated: 1817.85
        'arrearsBreakdown',
        'penaltyBreakdown',  // Should have 3 items now
        'totalAmount'
    ));
}

    public function getPreviousCurrentBill($clientId)
    {
        $billingDate = request()->query('billing_date', now());
        $billingDateCarbon = Carbon::parse($billingDate);

        $latestPayment = Payments::where('client_id', $clientId)
            ->where('billing_month', '<', $billingDateCarbon)
            ->latest('billing_month')
            ->first();

        $arrears = 0;
        if ($latestPayment) {
            $status = strtolower($latestPayment->status ?? '');
            $paid   = floatval($latestPayment->partial_payment_amount ?? 0);
            $current = floatval($latestPayment->current_bill ?? 0);
            $oldArrears = floatval($latestPayment->arrears ?? 0);

            if ($status === 'partial') {
                $arrears = max(0, $current - $paid) + $oldArrears;
            } elseif ($status === 'unpaid') {
                $arrears = $current + $oldArrears;
            }
        }

        return response()->json([
            'arrears'                   => round($arrears, 2),
            'remaining_current_balance' => $latestPayment->remaining_current_balance ?? 0,
        ]);
    }

    public function getLatestBilling($clientId)
{
    $latest = Billings::where('client_id', $clientId)
        ->orderBy('billing_date', 'desc')
        ->first();

    return response()->json([
        'previous_reading' => $latest?->present_reading ?? 0,
        'maintenance_cost' => $latest?->maintenance_cost ?? 0,
    ]);
}
}