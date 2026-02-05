<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->is('admin/*')) {
            Config::set('session.cookie', config('session.admin_cookie', 'laravel_admin_session'));
        }

        // Use view composer with deferred resolution
        View::composer('*', function ($view) {
            try {
                $asOf = today()->format('Y-m-d');
                
                $unseenLogs = \App\Models\ActivityLog::where('seen', 0)->count();
                $unreadReports = \App\Models\ProblemReport::where('is_read', 0)->count();

                $disconnectCount = \App\Models\Clients::whereHas('user', function ($userQ) use ($asOf) {
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

                $view->with([
                    'unseenLogs' => $unseenLogs,
                    'unreadReports' => $unreadReports,
                    'disconnectCount' => $disconnectCount,
                    'totalNotification' => $totalNotification,
                ]);
                
            } catch (\Exception $e) {
                // Log error but don't break the app
                \Log::error('View composer error: ' . $e->getMessage());
                
                $view->with([
                    'unseenLogs' => 0,
                    'unreadReports' => 0,
                    'disconnectCount' => 0,
                    'totalNotification' => 0,
                ]);
            }
        });
    }   
}