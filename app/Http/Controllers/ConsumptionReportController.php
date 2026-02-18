<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Models\Billings;
use Illuminate\Http\Request;

class ConsumptionReportController extends Controller
{
    public function index(Request $request)
    {
        $billDate = $request->filled('bill_date') ? $request->bill_date : null;

        /* If date filter is active, show individual bills instead of grouped consumers */
        if ($billDate) {
            return $this->getBillsByDate($request, $billDate);
        }

        /* Otherwise show grouped consumers (original behavior) */
        return $this->getGroupedConsumers($request);
    }

    /**
     * Show individual bills for a specific date
     */
    private function getBillsByDate(Request $request, $billDate)
    {
        $query = Billings::with('client')
            ->whereDate('billing_date', $billDate)
            ->whereHas('client', fn($q) => $q->whereNotNull('user_id'));

        /* text search on client */
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('meter_no', 'like', "%{$search}%");
            });
        }

        /* status filter on client */
        if ($request->filled('status')) {
            $query->whereHas('client', fn($q) => $q->where('status', $request->status));
        }

        $bills = $query->orderBy('billing_date', 'desc')->paginate(25)->withQueryString();

        return view('admin.consumption_report', [
            'bills' => $bills,
            'billDate' => $billDate,
            'viewMode' => 'bills_by_date'
        ]);
    }

    /**
     * Show grouped consumers (original behavior)
     */
    private function getGroupedConsumers(Request $request)
    {
        $query = Clients::with(['billings' => fn($q) => $q->latest()->take(3)])
            ->whereNotNull('user_id');

        /* text search */
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('meter_no', 'like', "%{$search}%");
            });
        }

        /* status filter */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consumers = $query->paginate(25)->withQueryString();

        foreach ($consumers as $c) {
            $c->total_consumption = $c->billings->sum('consumed');
        }

        return view('admin.consumption_report', [
            'consumers' => $consumers,
            'billDate' => null,
            'viewMode' => 'grouped'
        ]);
    }
}