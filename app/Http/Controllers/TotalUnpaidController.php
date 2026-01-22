<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class TotalUnpaidController extends Controller
{
   public function index()
{
    $unpaid = Payments::where('payments.status', 'unpaid')  // <-- add table name here
        ->join('clients', 'payments.client_id', '=', 'clients.id')
        ->orderBy('clients.full_name', 'asc')      // sort by client name
        ->orderBy('payments.billing_month', 'desc') // sort by billing month DESC
        ->select('payments.*')
        ->with('client')
        ->get();

    return view('admin.totals.total_unpaid', compact('unpaid')); 
}

public function unpaidConsumers(Request $request)
{
    $query = Payments::with('client')
        ->where('status', 'unpaid');

    // Filter by client name
    if ($request->filled('name')) {
        $query->whereHas('client', function ($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->name . '%');
        });
    }

    // Filter by billing month/year
    if ($request->filled('billing_month')) {
        $query->whereMonth('billing_month', Carbon::parse($request->billing_month)->month)
              ->whereYear('billing_month', Carbon::parse($request->billing_month)->year);
    }

    $unpaid = $query->get();

    return view('admin.totals.total_unpaid', compact('unpaid'));
}
public function printUnpaidConsumers(Request $request)
{
    $query = Payments::with('client')
        ->where('status', 'unpaid');

    if ($request->filled('name')) {
        $query->whereHas('client', function ($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->name . '%');
        });
    }

    if ($request->filled('billing_month')) {
        $query->whereMonth('billing_month', Carbon::parse($request->billing_month)->month)
              ->whereYear('billing_month', Carbon::parse($request->billing_month)->year);
    }

    $unpaid = $query->get();

    $pdf = PDF::loadView('admin.print_unpaid_consumers', compact('unpaid'));

    return $pdf->stream('unpaid_consumers.pdf');
}


}
