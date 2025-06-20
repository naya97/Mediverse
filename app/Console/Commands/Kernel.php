<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\NotificationAfterShiftService;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('appointments:send-reminders')->hourly();
        // $schedule->call(function () {
        //     app(NotificationAfterShiftService::class)->notifyDoctorsAfterShift();
        // })->dailyAt('15:00');

        // // تشغيل التابع الساعة 9 مساءً
        // $schedule->call(function () {
        //     app(NotificationAfterShiftService::class)->notifyDoctorsAfterShift();
        // })->dailyAt('21:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
