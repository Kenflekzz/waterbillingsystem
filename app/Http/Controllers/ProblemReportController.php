<?php

namespace App\Http\Controllers;

use App\Models\ProblemReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ProblemReportController extends Controller
{
    public function submit(Request $request)
    {
       $request->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'image'   => 'nullable|image|max:2048'
    ]);

    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('reports', 'public');
    }

    ProblemReport::create([
        'client_id'   => auth()->id(),
        'subject'     => $request->subject,
        'description' => $request->message,   // ← FIXED
        'image'       => $imagePath,
        'status'      => 'pending'
    ]);

        return back()->with('success', 'Your report has been sent successfully.');
    }
    public function showReportsPage()
    {
        $user = Auth::guard('user')->user();
        $reports = ProblemReport::where('client_id', $user->client_id)->get(); // or however you get the reports
        return view('user.reports-page', compact('reports'));
    }

}
