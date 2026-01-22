<?php

namespace App\Http\Controllers;

use App\Models\ProblemReport;
use Illuminate\Http\Request;
use App\Models\Notification;
class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search'); // search term
        $statusFilter = $request->input('status_filter'); // 'pending' or 'resolved'

        $reports = ProblemReport::with('client')
            ->leftJoin('clients', 'problem_reports.client_id', '=', 'clients.id')
            ->select('problem_reports.*') // only select problem_reports columns
            ->when($statusFilter, function ($query, $statusFilter) {
                // Prefix table to avoid ambiguity
                $query->where('problem_reports.status', $statusFilter);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('client', function ($q2) use ($search) {
                        $q2->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhere('problem_reports.subject', 'like', "%{$search}%")
                    ->orWhere('problem_reports.status', 'like', "%{$search}%");
                });
            })
            ->orderBy('clients.full_name', 'asc') // alphabetical by client name
            ->orderBy('problem_reports.created_at', 'desc') // newest first
            ->paginate(10)
            ->withQueryString(); // preserve search/filter params in pagination links
        
        ProblemReport::where('is_read', 0)->update(['is_read' => 1]);
        return view('admin.reports.index', compact('reports', 'search', 'statusFilter'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved',
        ]);

        $report = ProblemReport::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        if ($report->status == 'resolved') {
        Notification::create([
        'user_id' => $report->client_id,
        'type' => 'report_resolved',
        'related_id' => $report->id,
        'title' => "Your Report \"{$report->subject}\" Has Been Resolved",
        'message' => "Your submitted report titled \"{$report->subject  }\" has been marked as resolved by the admin."
    ]);

    }

        return back()->with('success', 'Report status updated successfully.');
    }
}
