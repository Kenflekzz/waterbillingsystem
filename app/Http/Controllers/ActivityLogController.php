<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function activityLog(Request $request)
    {
        $query = ActivityLog::with('client');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%");
            })
            ->orWhere('activity', 'like', "%$search%")
            ->orWhere('details', 'like', "%$search%");
        }

        // Order alphabetically by client full name, then by date descending
        $logs = $query->leftJoin('clients', 'activity_logs.user_id', '=', 'clients.user_id')
            ->select('activity_logs.*')
            ->orderBy('clients.full_name', 'asc')
            ->orderBy('activity_logs.created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // keep search query in pagination links

            
        ActivityLog::where('seen', 0)->update(['seen' => 1]);

        return view('admin.activity_log', compact('logs'));
    }
}
