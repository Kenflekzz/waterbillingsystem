<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserBilling;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payments;
use App\Models\Clients;

class UserController extends Controller
{
    /**
     * Show the user profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Show the user's billing section with all bills.
     */
    public function billing()
    {
        $user = Auth::user();

        $billings = UserBilling::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(5);

        // newest bill's arrears (admin already summed the past)
        $latestBill   = UserBilling::where('user_id', $user->id)
            ->latest()
            ->first();

        $totalArrears = $latestBill ? $latestBill->arrears : 0;   // ← single value
        $hasHighArrears = $totalArrears >= 10000;

        return view('user.billing', compact('billings', 'user', 'totalArrears', 'hasHighArrears'));
    }

    /**
     * Re-use the same arrears logic that admin uses.
     * $upToDate = usually today, or the newest bill date
     */
    private function getUserArrears(int $userId): float
    {
        $client = Clients::where('user_id', $userId)->first();
        return $client
            ? \App\Models\Payments::where('client_id', $client->id)->sum('arrears')
            : 0.0;
    }

    /**
     * Download the bill as a printable PDF.
     */
    public function printBill($id)
    {
        // Load both user and client relationships
        $billing = UserBilling::with(['user.client'])->findOrFail($id);
        $homepage = \App\Models\Homepage::first();

        // Get unpaid previous bills for arrears calculation
        $unpaidBills = UserBilling::where('user_id', $billing->user_id)
            ->where('id', '<', $billing->id)
            ->whereIn('status', ['unpaid', 'Unpaid', 'Overdue'])
            ->get();

        // Calculate total arrears
        $arrears = $unpaidBills->sum('current_bill');

        // Calculate penalty (₱0.005 per day late, only if actually late)
        $penalty = $unpaidBills->sum(function ($b) {
            $dueDate = \Carbon\Carbon::parse($b->due_date);
            $daysLate = now()->diffInDays($dueDate, false);
            return $daysLate > 0 ? $daysLate * 0.005 : 0;
        });

        // Calculate total amount due
        $totalAmount = $billing->current_bill 
            + $arrears 
            + $penalty 
            + ($billing->maintenance_cost ?? 0) 
            + ($billing->installation_fee ?? 0);

        // Build arrears breakdown for the table
        $arrearsBreakdown = $unpaidBills->map(function ($b) {
            return [
                'billing_month' => $b->billing_date,
                'current_bill'  => $b->current_bill,
            ];
        })->toArray();

        // Build penalty breakdown for the table
        $penaltyBreakdown = $unpaidBills->map(function ($b) {
            $dueDate = \Carbon\Carbon::parse($b->due_date);
            $daysLate = max(0, now()->diffInDays($dueDate, false));
            return [
                'billing_month'   => $b->billing_date,
                'due_date'        => $b->due_date,
                'days_late'       => $daysLate,
                'partial_penalty' => $daysLate * 0.005,
            ];
        })->toArray();

        // For "CONSUMER'S COPY" / "OFFICE COPY" labels
        $copyLabel = "CONSUMER'S COPY";

        $pdf = Pdf::loadView('user.billing_print', compact(
            'billing',
            'homepage',
            'arrears',
            'penalty',
            'totalAmount',
            'arrearsBreakdown',
            'penaltyBreakdown',
            'copyLabel'
        ));

        return $pdf->download('Billing_Statement_' . $billing->id . '.pdf');
    }

    /**
     * Handle online payment (GCash / PayMaya).
     */
    public function payBill($id, Request $request)
    {
        $billing = UserBilling::findOrFail($id);

        // In production, connect to PayMaya or GCash API here.
        // For now, just simulate successful payment.
        $billing->status = 'Paid';
        $billing->payment_method = $request->payment_method;
        $billing->payment_reference = 'PM-' . strtoupper(uniqid());
        $billing->payment_date = now();
        $billing->save();

        return back()->with('success', 'Payment successful via ' . $request->payment_method);
    }
}