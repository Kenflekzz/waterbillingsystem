<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use App\Models\ProblemReports;

class MyReportsController extends Controller
{
    // Fetch all reports (without pagination)
    public function getUserReports(Request $request)
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'reports' => [],
                'error' => 'User not authenticated'
            ], 401);
        }

        $perPage = 5;
        $page = $request->query('page', 1);

        $reports = $user->reports()->latest()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'reports' => $reports->items(),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage()
        ]);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json([
            'success' => false,
            'reports' => [],
            'error' => 'Failed to fetch reports'
        ], 500);
    }
}

}
