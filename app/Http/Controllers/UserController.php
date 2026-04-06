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

        $latestBill   = UserBilling::where('user_id', $user->id)
            ->latest()
            ->first();

        $totalArrears = $latestBill ? $latestBill->arrears : 0;
        $hasHighArrears = $totalArrears >= 10000;

        return view('user.billing', compact('billings', 'user', 'totalArrears', 'hasHighArrears'));
    }

    /**
     * Re-use the same arrears logic that admin uses.
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
        $billing = UserBilling::with(['user.client'])->findOrFail($id);
        $homepage = \App\Models\Homepage::first();

        $client = $billing->user->client;
        
        $billing->client = (object) [
            'full_name' => $client->full_name ?? ($billing->user->first_name . ' ' . $billing->user->last_name),
            'purok'     => $client->purok ?? 'N/A',
            'barangay'  => $client->barangay ?? 'N/A',
            'meter_no'  => $client->meter_no ?? $billing->user->meter_number ?? 'N/A',
        ];

        // Add missing fields that admin billing has
        $billing->reading_date    = $billing->reading_date ?? $billing->billing_date;
        $billing->previous_reading = $billing->previous_reading ?? 0;
        $billing->present_reading  = $billing->present_reading ?? 0;
        $billing->maintenance_cost = $billing->maintenance_cost ?? 0;
        $billing->installation_fee = $billing->installation_fee ?? 0;
        $billing->excess_hose      = $billing->excess_hose ?? 0;

        $unpaidBills = UserBilling::where('user_id', $billing->user_id)
            ->where('id', '<', $billing->id)
            ->whereIn('status', ['unpaid', 'Unpaid', 'Overdue'])
            ->get();

        $arrears = $unpaidBills->sum('current_bill');

        $penalty = $unpaidBills->sum(function ($b) {
            $dueDate  = \Carbon\Carbon::parse($b->due_date);
            $daysLate = now()->diffInDays($dueDate, false);
            return $daysLate > 0 ? $daysLate * 0.005 : 0;
        });

        $totalAmount = $billing->current_bill
            + $arrears
            + $penalty
            + $billing->maintenance_cost
            + $billing->installation_fee;

        $arrearsBreakdown = $unpaidBills->map(function ($b) {
            return [
                'billing_month' => $b->billing_date,
                'current_bill'  => $b->current_bill,
            ];
        })->toArray();

        $penaltyBreakdown = $unpaidBills->map(function ($b) {
            $dueDate  = \Carbon\Carbon::parse($b->due_date);
            $daysLate = max(0, now()->diffInDays($dueDate, false));
            return [
                'billing_month'   => $b->billing_date,
                'due_date'        => $b->due_date,
                'days_late'       => $daysLate,
                'partial_penalty' => $daysLate * 0.005,
            ];
        })->toArray();

        $copyLabel = "CONSUMER'S COPY";

        // Return view instead of PDF download
        return view('user.billing_print', compact(
            'billing',
            'homepage',
            'arrears',
            'penalty',
            'totalAmount',
            'arrearsBreakdown',
            'penaltyBreakdown',
            'copyLabel'
        ));
    }

    /**
     * Handle online payment (GCash / PayMaya).
     */
    public function payBill($id, Request $request)
    {
        $billing = UserBilling::findOrFail($id);

        $billing->status = 'Paid';
        $billing->payment_method = $request->payment_method;
        $billing->payment_reference = 'PM-' . strtoupper(uniqid());
        $billing->payment_date = now();
        $billing->save();

        return back()->with('success', 'Payment successful via ' . $request->payment_method);
    }
}