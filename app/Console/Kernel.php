<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CleanupOldData;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:deleteunpaidbookings')
            ->dailyAt('12:03');

        // create invoice for hotel sunscription
        $schedule->command('invoices:create-new-invoices-daily')->daily();


        //disbale expired domain
        $schedule->command('update:disable-expired-hotel')->daily();

        // Run application optimization during low-traffic periods (e.g., 3 AM daily)
        $schedule->command('app:optimize --full')
            ->dailyAt('03:00')
            ->environments(['production'])
            ->runInBackground()
            ->withoutOverlapping();

        // Clean up old data weekly
        $schedule->command('queue:work --once --queue=cleanup')
            ->weekly()
            ->sundays()
            ->at('04:00')
            ->environments(['production'])
            ->runInBackground()
            ->withoutOverlapping()
            ->before(function () {
                dispatch(new CleanupOldData(90))->onQueue('cleanup');
            });

        // Quick database optimization daily
        $schedule->command('app:optimize --optimize-db')
            ->dailyAt('04:30')
            ->environments(['production'])
            ->runInBackground()
            ->withoutOverlapping();
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
