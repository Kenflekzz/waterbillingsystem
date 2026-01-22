<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\Request;

class ConsumptionReportController extends Controller
{
    public function index(Request $request)
{
    /* 1. ALL consumers (no status filter here) */
    $query = Clients::with(['billings' => fn($q) => $q->latest()->take(3)])
                    ->whereNotNull('user_id');   // << status removed

    /* ---------- text search ---------- */
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('meter_no', 'like', "%{$search}%");
        });
    }

    /* ---------- status filter (from view) ---------- */
    if ($request->filled('status')) {
        $query->where('status', $request->status);   // CURC / CUT / anything
    }

    /* ---------- single-date filter (bill issued on) ---------- */
    if ($request->filled('bill_date')) {
        $query->whereHas('billings', fn($q) =>
            $q->whereDate('billing_date', $request->bill_date)
        );
    }

    $consumers = $query->paginate(25)->withQueryString();

    /* virtual total */
    foreach ($consumers as $c) {
        $c->total_consumption = $c->billings->sum('consumed');
    }

    return view('admin.consumption_report', compact('consumers'));
}
}