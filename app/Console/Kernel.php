<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
{
    // Admin digest at 12:00 MYT
    $schedule->command('items:send-deadline-reminders')
             ->dailyAt('12:00')
             ->timezone('Asia/Kuala_Lumpur');

    // User-specific digests at 12:00 MYT
    $schedule->call(function () {
        $users = \App\Models\User::whereNotNull('email')
            ->where('role', '!=', 'admin')
            ->get();

        foreach ($users as $user) {
            \App\Jobs\SendUserDigestJob::dispatch($user->id);
        }
    })->dailyAt('12:00')->timezone('Asia/Kuala_Lumpur');
}


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
