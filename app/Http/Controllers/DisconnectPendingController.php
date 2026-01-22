<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Users;

class DisconnectPendingController extends Controller
{
    public function index(Request $request)
    {
        $asOf   = $request->input('as_of');   // YYYY-MM-DD
        $search = trim($request->input('search', ''));

        $users = Users::with('billings')
            ->get()
            ->filter(fn ($u) => $u->client);

        $users = $users->filter(function ($user) use ($asOf) {

            // Get unpaid / overdue bills only
            $unpaid = $user->billings
                ->whereIn('status', ['unpaid', 'Overdue'])
                ->sortByDesc('created_at')
                ->values();

            if ($unpaid->count() < 3) {
                return false;
            }

            // 3rd unpaid bill
            $thirdBillDate = $unpaid[2]->created_at->toDateString();

            // Store for blade display
            $user->third_unpaid_date = $thirdBillDate;

            // If date picker is used → EXACT MATCH
            if ($asOf) {
                return $thirdBillDate === $asOf;
            }

            return true;
        });

        // Search filter
        if ($search !== '') {
            $users = $users->filter(function ($u) use ($search) {
                return str_contains(strtolower($u->client->full_name ?? ''), strtolower($search)) ||
                       str_contains(strtolower($u->client->meter_no ?? ''), strtolower($search)) ||
                       str_contains(strtolower($u->email ?? ''), strtolower($search));
            });
        }

        // Pagination
        $perPage = 25;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');

        $consumers = new \Illuminate\Pagination\LengthAwarePaginator(
            $users->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $users->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.disconnect_pending', compact('consumers'));
    }
}
