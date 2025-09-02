<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payments;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    
    public function index(Request $request)
{
    $filterStatus = $request->input('status');
    $billingDate = $request->input('billing_date'); // single date

    $reports = DB::table('billings')
        ->join('clients', 'billings.client_id', '=', 'clients.id')
        ->leftJoin('payments', function ($join) {
            $join->on('billings.client_id', '=', 'payments.client_id')
                ->on(DB::raw('MONTH(billings.billing_date)'), '=', DB::raw('MONTH(payments.billing_month)'))
                ->on(DB::raw('YEAR(billings.billing_date)'), '=', DB::raw('YEAR(payments.billing_month)'));
        })
        ->select(
            'billings.billing_id',
            'clients.meter_no',
            'clients.full_name',
            'billings.billing_date',
            'billings.previous_reading',
            'billings.present_reading',
            'billings.consumed',
            'billings.current_bill',
            'payments.arrears',
            'billings.total_penalty',
            'billings.total_amount',
            'payments.status'
        )
        ->when($filterStatus, fn($q) => $q->where('payments.status', $filterStatus))
        ->when($billingDate, fn($q) => $q->whereDate('billings.billing_date', $billingDate))
        ->orderBy('billings.billing_date', 'desc')
        ->paginate(10);

    return view('admin.reports', compact('reports', 'filterStatus', 'billingDate'));
}


    public function print(Request $request)
{
    $query = Payments::query()
        ->leftJoin('billings', 'payments.billing_month', '=', 'billings.billing_date')
        ->leftJoin('clients', 'billings.client_id', '=', 'clients.id')
        ->select([
            'billings.*',
            'clients.full_name',
            'clients.meter_no',
            'payments.status',
            'payments.arrears',
            'payments.penalty as total_penalty',
            'payments.total_amount'
        ]);

    if ($request->has('status') && $request->status !== '') {
        $query->where('payments.status', $request->status);
    }

    if ($request->has('billing_date') && $request->billing_date !== '') {
        $query->whereDate('billings.billing_date', $request->billing_date);
    }

    $reports = $query->get(); // Chunking to avoid memory issues with large datasets

    // Pass filters to blade if you need to display them
    $status = $request->status;
    $billing_date = $request->billing_date;

    $pdf = Pdf::loadView('admin.print_reports', compact('reports', 'status', 'billing_date'));

    $pdf->setPaper('A4', 'landscape');
    return $pdf->stream('reports.pdf');
}




}

