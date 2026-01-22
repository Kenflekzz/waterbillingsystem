<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TotalPaidController extends Controller
{
    public function index()
{
    $totalPaid = Payments::with('client')
        ->join('clients', 'payments.client_id', '=', 'clients.id')
        ->whereIn('payments.status', ['paid', 'paid via gcash'])
        ->select('payments.*')
        ->orderBy('clients.full_name')          // alphabetical
        ->orderBy('payments.billing_month', 'desc')
        ->paginate(50);                         // optional pagination

    return view('admin.totals.total_paid', compact('totalPaid'));
}

    public function totalPaid(Request $request)
    {
        /* 1.  base set + join so we can sort by client name */
        $query = Payments::with('client')
                         ->join('clients', 'payments.client_id', '=', 'clients.id')
                         ->whereIn('payments.status', ['paid', 'paid via gcash'])
                         ->select('payments.*');

        /* 2.  optional filters */
        if ($request->filled('name')) {
            $query->where('clients.full_name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('billing_month')) {
            $month = Carbon::createFromFormat('Y-m', $request->billing_month);
            $query->whereMonth('payments.billing_month', $month->month)
                  ->whereYear('payments.billing_month', $month->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'gcash') {
                $query->where('payments.payment_type', 'gcash');
            } else {
                $query->where('payments.status', $request->status);
            }
        }

        /* 3.  alpha by client, then newest bill first */
        $totalPaid = $query->orderBy('clients.full_name')
                           ->orderBy('payments.billing_month', 'desc')
                           ->paginate(50);

        return view('admin.totals.total_paid', compact('totalPaid'));
    }

    public function printTotalPaid(Request $request)
    {
        /* same base query and filters */
        $query = Payments::with('client')
                         ->join('clients', 'payments.client_id', '=', 'clients.id')
                         ->whereIn('payments.status', ['paid', 'paid via gcash'])
                         ->select('payments.*');

        if ($request->filled('name')) {
            $query->where('clients.full_name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('billing_month')) {
            $month = Carbon::createFromFormat('Y-m', $request->billing_month);
            $query->whereMonth('payments.billing_month', $month->month)
                  ->whereYear('payments.billing_month', $month->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'gcash') {
                $query->where('payments.payment_type', 'gcash');
            } else {
                $query->where('payments.status', $request->status);
            }
        }

        $totalPaid = $query->orderBy('clients.full_name')
                           ->orderBy('payments.billing_month', 'desc')
                           ->get();

        $pdf = PDF::loadView('admin.print_paid_consumers', compact('totalPaid'));
        return $pdf->stream('total_paid_report.pdf');
    }
}