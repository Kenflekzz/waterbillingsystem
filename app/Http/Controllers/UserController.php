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

    // newest bill’s arrears (admin already summed the past)
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
        $billing = UserBilling::findOrFail($id);

        $pdf = Pdf::loadView('user.billing_print', compact('billing'));
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

    /**public function consumption()
    {
        $user = Auth::guard('user')->user();

        // Get the two most recent consumption records
        $latestData = DB::table('behavioral_data')
            ->where('metric_name', 'consumption')
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        $currentConsumption = $latestData->first()->value ?? 0;
        $previousConsumption = $latestData->skip(1)->first()->value ?? 0;

        return view('user.consumption', compact('user', 'currentConsumption', 'previousConsumption'));
    }**/

}
