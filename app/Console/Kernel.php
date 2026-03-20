<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check alerts every hour so budgets and installments stay visible
        $schedule->command('finance:check-alerts')->hourly();

        // Send weekly summary every Sunday at 9 AM
        $schedule->command('finance:weekly-summary')->weeklyOn(0, '09:00');

        // Send monthly summary on the 1st of each month at 9 AM
        $schedule->command('finance:monthly-summary')->monthlyOn(1, '09:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
