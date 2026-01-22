<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ProblemReport;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Config;
use App\Models\UserBilling;
use App\Models\Clients;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /* ----------  separate admin / user session cookies  ---------- */
        if (request()->is('admin/*')) {
            Config::set('session.cookie', config('session.admin_cookie', 'laravel_admin_session'));
        }
        View::composer('*', function ($view) {
        $asOf = today()->format('Y-m-d');   // <-- add this line

        $unseenLogs = ActivityLog::where('seen', 0)->count();
        $unreadReports = ProblemReport::where('is_read', 0)->count();

        // ---- correct count ----
            $disconnectCount = Clients::whereHas('user', function ($userQ) use ($asOf) {
                $userQ->whereHas('billings', function ($billQ) use ($asOf) {
                    $billQ->whereDate('created_at', '<=', $asOf)
                        ->whereIn('status', ['unpaid', 'Overdue']);
                }, '>=', 3);
            })
            ->where(function ($q) {
                $q->whereNull('is_disconnect_seen')
                ->orWhere('is_disconnect_seen', 0)
                ->orWhereDate('is_disconnect_seen', '<', today());
            })
            ->count();
        $totalNotification = $unseenLogs + $unreadReports + $disconnectCount;

        $view->with('unseenLogs', $unseenLogs)
            ->with('unreadReports', $unreadReports)
            ->with('disconnectCount', $disconnectCount)
            ->with('totalNotification', $totalNotification);
    });
    }   
}