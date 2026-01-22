<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /** -----------------------------
     *  INDEX: List Payments
     * -----------------------------*/
    public function index()
    {
        $payments = Payments::with('client')
            ->join('clients', 'payments.client_id', '=', 'clients.id')
            ->orderBy('clients.full_name', 'asc')
            ->orderByDesc('payments.billing_month') // optional secondary sort
            ->select('payments.*') // VERY IMPORTANT to avoid column conflicts
            ->paginate(25);

        // Attach computed fields
        foreach ($payments as $payment) {
            $payment->full_name = $payment->client->full_name ?? 'N/A';
            $payment->barangay = $payment->client->barangay ?? 'N/A';
            $payment->purok = $payment->client->purok ?? 'N/A';
        }

        return view('admin.payments', compact('payments'));
    }

    /** -----------------------------
     *  EDIT PAGE
     * -----------------------------*/
    public function edit($id)
    {
        $payment = Payments::findOrFail($id);
        return view('admin.payments.edit', compact('payment'));
    }

    /** -----------------------------
     *  UPDATE PAYMENT
     * -----------------------------*/
    public function update(Request $request, $id)
{
    // Find payment
    $payment = Payments::findOrFail($id);

    // 🔐 Prevent editing GCash-completed payments
    if ($payment->payment_type === 'gcash' && $payment->status === 'paid via gcash') {
        $message = 'GCash payments cannot be modified.';
        if ($request->ajax()) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'message' => $message], 403);
        }
        return redirect()->route('admin.payments.index')
            ->with('error', $message);
    }

    // ✅ Store OLD status (needed for reconnection logic)
    $oldStatus = $payment->status;

    // Validate request
    $validated = $request->validate([
        'status' => 'required|in:paid,unpaid,disconnected,partial,reconnected,paid via gcash',
        'payment_type' => 'nullable|in:none,arrears_only,full,partial_current,gcash',
        'partial_payment_amount' => 'nullable|numeric|min:0'
    ]);

    $paymentType   = $request->payment_type;
    $partialAmount = $request->partial_payment_amount ?? 0;

    switch ($paymentType) {

        case 'arrears_only':
            $payment->partial_payment_amount = $payment->arrears ?? 0;
            $payment->payment_type = 'arrears_only';
            $payment->status = 'partial';
            break;

        case 'full':
            $payment->partial_payment_amount = 0;
            $payment->payment_type = 'full';
            $payment->status = 'paid';
            $payment->remaining_current_balance = 0;
            break;

        case 'partial_current':
            $paid = (float) $partialAmount;
            $current = (float) $payment->current_bill;

            if ($paid > $current) {
                return redirect()->back()
                    ->withErrors(['partial_payment_amount' => 'Partial amount cannot exceed current bill.'])
                    ->withInput();
            }

            $remaining = $current - $paid;

            $payment->partial_payment_amount = $paid;
            $payment->remaining_current_balance = $remaining;
            $payment->payment_type = 'partial_current';
            $payment->status = $remaining > 0 ? 'partial' : 'paid';
            break;

        case 'gcash':
            $payment->payment_type = 'gcash';
            $payment->status = 'paid';
            $payment->payment_method = 'GCash';
            $payment->partial_payment_amount = $payment->total_amount;
            $payment->remaining_current_balance = 0;

            $formattedMonth = Carbon::parse($payment->billing_month)->format('M Y');

            ActivityLog::create([
                'user_id'  => $payment->client->user_id ?? null,
                'activity' => 'Payment via GCash',
                'details'  => "User {$payment->client->full_name} paid ₱{$payment->total_amount} via GCash for billing month {$formattedMonth}"
            ]);
            break;

        case 'none':
            $payment->payment_type = 'none';
            $payment->partial_payment_amount = 0;
            $payment->status = $validated['status'];
            break;

        default:
            $payment->status = $validated['status'];
            break;
    }

    // ===============================
    // 🔁 RECONNECTION LOGIC (NEW)
    // ===============================
    if ($oldStatus === 'disconnected' && $payment->status === 'reconnected') {

        // Apply reconnection fee ONCE
        $payment->reconnection_fee = 250;

        // Optional: include fee in total amount
        $payment->total_amount += 250;

        ActivityLog::create([
            'user_id'  => $payment->client->user_id ?? null,
            'activity' => 'Service Reconnected',
            'details'  => "Consumer {$payment->client->full_name} was reconnected. Reconnection fee ₱250 applied."
        ]);

    } elseif ($payment->status !== 'reconnected') {
        // Reset fee if not reconnected
        $payment->reconnection_fee = null;
    }
    // 🔗 ADD reconnection fee to LAST UNPAID BILL (safe)
    if (
        $oldStatus === 'disconnected' &&
        $payment->status === 'reconnected' &&
        $payment->user_billing_id &&
        $payment->userBilling
    ) {
        $billing = $payment->userBilling;

        $billing->total_amount += 250;

        // Optional: track under penalties (no schema change needed)
        $billing->total_penalty = ($billing->total_penalty ?? 0) + 250;

        $billing->save();
    }


    // SAVE PAYMENT
    $payment->save();

    // ======  AUTO-UPDATE CLIENT STATUS  ======
    $client = $payment->client;
    if ($client) {
        switch ($payment->status) {
            case 'disconnected':
                $client->status = 'CUT';
                $client->date_cut = now()->toDateString();   // optional
                break;

            case 'reconnected':
                $client->status = 'CURC';
                $client->date_cut = null;
                break;
        }
        $client->save();
    }

    // ===============================
    // 🔄 AUTO-UPDATE USER BILLING
    // ===============================
    if ($payment->user_billing_id) {
        $userBilling = $payment->userBilling;

        if ($userBilling) {

            if ($payment->status === 'paid' && $payment->payment_type !== 'gcash') {
                $userBilling->status = 'paid';
                $userBilling->payment_method = 'Walk-in';
                $userBilling->payment_date = now();
            }

            if ($payment->status === 'partial') {
                $userBilling->status = 'Partially Paid';
                $userBilling->payment_method = 'Walk-in';
                $userBilling->payment_date = now();
            }

            if ($payment->status === 'disconnected') {
                $userBilling->status = 'Disconnected';
            }

            // ✅ Reconnected sync
            if ($payment->status === 'reconnected') {
                $userBilling->status = 'Reconnected';
                $userBilling->payment_method = 'Walk-in';
                $userBilling->payment_date = now();
            }

            if ($payment->payment_type === 'gcash' && $payment->status === 'paid') {
                $userBilling->status = 'paid';
                $userBilling->payment_method = 'GCash';
                $userBilling->payment_date = now();
            }

            $userBilling->save();
        }
    }

    // SweetAlert response
    $swalMessage = 'Payment updated successfully.';

    if ($request->ajax()) {
        return response()->json([
            'icon' => 'success',
            'title' => 'Success',
            'message' => $swalMessage
        ]);
    }

    return redirect()->route('admin.payments.index')
        ->with('success', $swalMessage);
}



    /** -----------------------------
     *  SAFE DATE FORMATTER
     * -----------------------------*/
    private function safeFormatMonth($month)
    {
        try {
            return Carbon::parse($month)->format('M Y');
        } catch (\Throwable $e) {
            return $month; // return raw value if invalid date
        }
    }

    /** -----------------------------
     *  DELETE PAYMENT
     * -----------------------------*/
    public function destroy(string $id)
{
    $payment = Payments::findOrFail($id);
    $payment->delete();

    if(request()->ajax()) {
        return response()->json([
            'icon' => 'success',
            'title' => 'Deleted!',
            'message' => 'Payment deleted successfully.'
        ]);
    }

    return redirect()->route('admin.payments.index')
        ->with('success', 'Payment deleted successfully.');
}

}
