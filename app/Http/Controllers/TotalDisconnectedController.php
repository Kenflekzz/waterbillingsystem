<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TotalDisconnectedController extends Controller
{
    /* first landing page – no filters */
    public function index()
    {
        $totalDisconnected = Payments::with('client')
            ->join('clients', 'payments.client_id', '=', 'clients.id')
            ->where('payments.status', 'disconnected')
            ->select('payments.*')
            ->orderBy('clients.full_name')
            ->orderBy('payments.billing_month', 'desc')
            ->paginate(50);

        return view('admin.totals.total_disconnected', compact('totalDisconnected'));
    }

    /* filtered list (name + month) */
    public function filter(Request $request)
    {
        $query = Payments::with('client')
            ->join('clients', 'payments.client_id', '=', 'clients.id')
            ->where('payments.status', 'disconnected')
            ->select('payments.*');

        // ---- name ----
        if ($request->filled('name')) {
            $query->where('clients.full_name', 'like', '%'.$request->name.'%');
        }

        // ---- month ----
        if ($request->filled('billing_month')) {
            $month = Carbon::createFromFormat('Y-m', $request->billing_month);
            $query->whereMonth('payments.billing_month', $month->month)
                  ->whereYear('payments.billing_month', $month->year);
        }

        $totalDisconnected = $query->orderBy('clients.full_name')
                                   ->orderBy('payments.billing_month', 'desc')
                                   ->paginate(50)
                                   ->withQueryString();   // keep filter string in links

        return view('admin.totals.total_disconnected', compact('totalDisconnected'));
    }

    /* PDF of the same filters */
    public function print(Request $request)
    {
        $query = Payments::with('client')
            ->join('clients', 'payments.client_id', '=', 'clients.id')
            ->where('payments.status', 'disconnected')
            ->select('payments.*');

        if ($request->filled('name')) {
            $query->where('clients.full_name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('billing_month')) {
            $month = Carbon::createFromFormat('Y-m', $request->billing_month);
            $query->whereMonth('payments.billing_month', $month->month)
                  ->whereYear('payments.billing_month', $month->year);
        }

        $totalDisconnected = $query->orderBy('clients.full_name')
                                   ->orderBy('payments.billing_month', 'desc')
                                   ->get();

      $sheet = $request->input('paper','a4');   // a4 or letter
        $ori   = $request->input('orientation','landscape');

        $pdf = PDF::loadView('admin.print_disconnected_consumers', compact('totalDisconnected'))
                ->setPaper($sheet, $ori);
        return $pdf->stream('disconnected_consumers.pdf');
    }
}