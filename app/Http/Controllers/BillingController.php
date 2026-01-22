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
            ->orderBy('clients.full_name', 'asc')     // ✅ sort alphabetically
            ->orderBy('billing_date', 'desc')         // optional secondary sort
            ->select('billings.*')                    // prevents column collision
            ->paginate(25);

        $clients = Clients::all();

        return view('admin.billings', compact('billings', 'clients'));
    }


public function store(Request $request)
{
    $validated = $request->validate([
        'client_id'         => 'required|exists:clients,id',
        'billing_date'      => 'required|date',
        'due_date'          => 'required|date',
        'reading_date'      => 'required|date',
        'previous_reading'  => 'required|numeric|min:0',
        'present_reading'   => 'required|numeric|min:0',
        'current_bill'      => 'required|numeric|min:0',
        'maintenance_cost'  => 'nullable|numeric|min:0',
        'installation_fee'  => 'nullable|numeric|min:0',
    ]);

    $validated['maintenance_cost'] = $validated['maintenance_cost'] ?? 0;
    $validated['installation_fee'] = $validated['installation_fee'] ?? 0;

    // 1️⃣ Calculate water charge (tier table)
    $cubicMetres = max(0, $validated['present_reading'] - $validated['previous_reading']);
    $waterCharge = 0;
    $remaining   = $cubicMetres;

    if ($remaining > 0) {
        $waterCharge += 150; $remaining -= 10;
        if ($remaining > 0) { $step = min(10, $remaining); $waterCharge += $step*16; $remaining -= $step; }
        if ($remaining > 0) { $step = min(10, $remaining); $waterCharge += $step*19; $remaining -= $step; }
        if ($remaining > 0) { $step = min(10, $remaining); $waterCharge += $step*23; $remaining -= $step; }
        if ($remaining > 0) { $step = min(10, $remaining); $waterCharge += $step*26; $remaining -= $step; }
        if ($remaining > 0) { $waterCharge += $remaining*30; }
    } else {
        $waterCharge = 150;
    }

    // 2️⃣ Correct arrears logic
    $billingDate = Carbon::parse($validated['billing_date']);
    $arrears = 0;
    $penalty = 0;

    $previous = Payments::where('client_id', $validated['client_id'])
        ->where('billing_month', '<', $billingDate)
        ->orderBy('billing_month', 'desc')
        ->first();

    if ($previous) {
        $status = strtolower($previous->status);
        $ptype  = strtolower($previous->payment_type ?? '');
        $currentBill = floatval($previous->current_bill);
        $paidAmount  = floatval($previous->partial_payment_amount ?? 0);
        $prevArrears = floatval($previous->arrears ?? 0);

        if ($status === 'paid' || $ptype === 'full' || $ptype === 'gcash') {
            $arrears = 0;
        } elseif ($status === 'partial') {
            $arrears = max(0, ($currentBill + $prevArrears) - $paidAmount);
        } elseif ($status === 'unpaid') {
            $arrears = $currentBill + $prevArrears;
        }

        if ($arrears > 0) {
            $dueDate = Carbon::parse($previous->billing_month)->addDays(14);
            $daysLate = $dueDate->diffInDays($billingDate, false);
            if ($daysLate > 0) {
                $penalty = round($arrears * 0.005 * $daysLate, 2);
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

    $client = Clients::find($validated['client_id']);
    if ($client && $client->user_id) {
        // Create UserBilling
        $userBilling = UserBilling::create([
            'user_id'      => $client->user_id,
            'bill_number'  => "C{$client->id}-BILL-{$billingId}", // per-client billing number
            'billing_date' => $billing->billing_date,
            'due_date'     => $billing->due_date,
            'amount_due'   => $totalAmount,
            'current_bill' => $waterCharge,
            'arrears'      => $arrears,
            'penalty'      => $penalty,
            'consumed'     => $cubicMetres,
            'status'       => 'unpaid',
        ]);

        // Create matching payment row
        Payments::create([
            'client_id'       => $client->id,
            'user_billing_id' => $userBilling->id,
            'billing_month'   => $billing->billing_date,
            'current_bill'    => $waterCharge,
            'arrears'         => $arrears,
            'penalty'         => $penalty,
            'total_amount'    => $totalAmount,
            'partial_payment_amount' => 0,
            'payment_type'    => 'N/A',
            'status'          => 'unpaid',
        ]);

        // Notification
        Notification::create([
            'user_id' => $client->id,
            'type' => 'billing',
            'related_id' => $billing->id,
            'title' => "New Billing Issued: #{$billingId}",
            'message' => "A new billing statement (Bill #{$billingId}) has been issued to your account."
        ]);
    }

    UserBilling::where('user_id', $client->user_id)->update(['updated_at' => now()]);

    return redirect()->route('admin.billings.index')
        ->with('success', 'Billing and payment record created successfully.');
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

    // --- 1.  Get ALL bills before this billing date --------------------------
    $history = Payments::where('client_id', $clientId)
        ->where('billing_month', '<', $billingDateCarbon)
        ->orderBy('billing_month', 'asc')
        ->get();

    $penalty = 0;

    foreach ($history as $bill) {
        // Skip if this bill was already paid in full or via gcash
        if (in_array($bill->payment_type, ['full', 'gcash']) && $bill->status === 'paid') {
            continue;
        }

        // Amount that is still unpaid on this bill
        $unpaid = 0;
        if ($bill->status === 'unpaid') {
            $unpaid = floatval($bill->current_bill);
        } elseif ($bill->status === 'partial') {
            $unpaid = max(0, floatval($bill->current_bill) - floatval($bill->partial_payment_amount ?? 0));
        }

        if ($unpaid <= 0) continue;

        // --- 2.  Was this bill covered by a LATER full payment? --------------
        $laterFull = Payments::where('client_id', $clientId)
            ->where('billing_month', '>', $bill->billing_month)
            ->where('billing_month', '<', $billingDateCarbon)
            ->where('payment_type', 'full')
            ->where('status', 'paid')
            ->exists();

        if ($laterFull) continue; // ✅ bill was settled by a later full payment

        // --- 3.  Penalise only if still outstanding --------------------------
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

            // Skip fully paid / full / GCash
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

    // ================================
    // Get past unpaid payments for arrears
    // ================================
    $pastUnpaidPayments = Payments::where('client_id', $billing->client_id)
        ->where('status', 'unpaid')
        ->where('billing_month', '<', $billing->billing_date)
        ->orderBy('billing_month', 'desc')
        ->get()
        ->toArray();

    $arrears = array_sum(array_column($pastUnpaidPayments, 'current_bill'));

    $arrearsBreakdown = array_map(function ($payment) {
        return [
            'billing_month' => $payment['billing_month'],
            'current_bill'  => floatval($payment['current_bill']),
        ];
    }, $pastUnpaidPayments);

    // ================================
    // Calculate penalty
    // ================================
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

    // ================================
    // Reconnection Fee Logic
    // ================================
    $lastTwoBills = Billings::where('client_id', $billing->client_id)
        ->where('id', '<', $billing->id)
        ->orderByDesc('billing_date')
        ->take(2)
        ->get();

    $consecutiveUnpaid = 0;
    foreach ($lastTwoBills as $b) {
        if (in_array($b->status ?? 'unpaid', ['unpaid', 'disconnected'])) {
            $consecutiveUnpaid++;
        } else {
            break;
        }
    }
    $reconnectionFee = ($consecutiveUnpaid === 2) ? 250 : 0;

    // ================================
    // Total Amount (for both copies)
    // ================================
    $totalAmount = $billing->current_bill + $arrears + $penalty +
                   $billing->maintenance_cost + $billing->installation_fee +
                   $reconnectionFee;

    // ================================
    // Return view with everything needed
    // ================================
    return view('admin.print', compact(
        'billing',
        'arrears',
        'penalty',
        'arrearsBreakdown',
        'penaltyBreakdown',
        'reconnectionFee',
        'totalAmount' // send totalAmount to Blade
    ));
}



    public function getPreviousCurrentBill($clientId)
{
    $billingDate = request()->query('billing_date', now());
    $billingDateCarbon = Carbon::parse($billingDate);

    // most-revious bill (any status)
    $latestPayment = Payments::where('client_id', $clientId)
        ->where('billing_month', '<', $billingDateCarbon)
        ->latest('billing_month')
        ->first();

    // true past-due arrears
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
        } // paid / gcash → 0
    }

    return response()->json([
        'arrears'                   => round($arrears, 2),
        'remaining_current_balance' => $latestPayment->remaining_current_balance ?? 0,
    ]);
}

}
