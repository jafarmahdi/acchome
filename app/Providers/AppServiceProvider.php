<?php

namespace App\Providers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share unread alerts count with all views
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                app(AlertService::class)->refreshTimedAlertsForFamily(Auth::user()->family_id);

                $unreadAlerts = Alert::where('family_id', Auth::user()->family_id)
                    ->unread()
                    ->count();
                $view->with('unreadAlerts', $unreadAlerts);
            }
        });
    }
}
