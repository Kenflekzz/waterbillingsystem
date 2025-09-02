<?php

namespace App\Http\Controllers;
use App\Models\Payments; // Assuming you have a Payments model

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payments::with('client')
        ->orderBy('billing_month', 'desc')
        ->paginate(25);

        foreach ($payments as $payment) {
        $payment->full_name = $payment->client->full_name ?? 'N/A';
        $payment->barangay = $payment->client->barangay ?? 'N/A';
        $payment->purok = $payment->client->purok ?? 'N/A';
        }
        return view('admin.payments', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit($id)
    {
        // Find the payment by ID or fail
        $payment = Payments::findOrFail($id);

        // If needed, you can also load related client data:
        // $payment->load('client');

        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        // Validate basic fields
        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid,disconnected,partial',
            'payment_type' => 'nullable|in:arrears_only,full,partial_current',
            'partial_payment_amount' => 'nullable|numeric|min:0'
        ]);

        $payment = Payments::findOrFail($id);

        $paymentType = $request->payment_type;
        $partialPayment = $request->partial_payment_amount ?? 0;

        if ($paymentType === 'arrears_only') {
            // Consumer decides to pay all arrears only
            $payment->partial_payment_amount = $payment->arrears;
            $payment->payment_type = 'arrears_only';
            $payment->status = 'partial'; // still partial since current bill unpaid
        }
        elseif ($paymentType === 'full') {
            // Consumer pays total_amount fully
            $payment->partial_payment_amount = $payment->total_amount;
            $payment->payment_type = 'full';
            $payment->status = 'paid';
        }
        elseif ($paymentType === 'partial_current') {
            // Consumer pays part of current bill; remaining unpaid part becomes new arrears
            if ($partialPayment < $payment->current_bill) {
                $balance = $payment->current_bill - $partialPayment;
                $payment->remaining_current_balance = $balance;
                $payment->partial_payment_amount = $partialPayment;
                $payment->payment_type = 'partial_current';
                $payment->status = 'partial';
            } else {
                // Paid full current bill
                $payment->partial_payment_amount = $payment->current_bill;
                $payment->payment_type = 'partial_current';
                $payment->status = 'paid';
            }
        }
        else {
            // fallback: just use provided status
            $payment->status = $validated['status'];
        }

        $payment->save();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment updated successfully.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payments = Payments::findOrFail($id);
        $payments->delete();
        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted successfully.');
    }

}
