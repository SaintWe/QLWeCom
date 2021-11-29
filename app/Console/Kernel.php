<?php

namespace App\Console;

use App\Console\WskeyUpdate;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        WskeyUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('jd:wskey_update')->twiceDaily(7, 19)->runInBackground();
        $schedule->command('jd:del_undefened_users')->twiceDaily(1)->runInBackground();
    }
}
