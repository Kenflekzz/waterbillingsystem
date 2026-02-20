<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;

class TotalSubscribersController extends Controller
{
    public function index(Request $request)
    {
        $query = Clients::orderBy('full_name', 'asc');

        // Apply status filter if provided
        if ($request->has('status') && in_array($request->status, ['CUT', 'CURC'])) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();
        $currentFilter = $request->status ?? 'all';

        return view('admin.totals.total_subscribers', compact('subscribers', 'currentFilter'));
    }

    public function print(Request $request)
    {
        $query = Clients::orderBy('full_name', 'asc');

        // Apply same filter logic
        if ($request->has('status') && in_array($request->status, ['CUT', 'CURC'])) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();
        $currentFilter = $request->status ?? 'all';

        return view('admin.print_subscribers', compact('subscribers', 'currentFilter'));
    }
}