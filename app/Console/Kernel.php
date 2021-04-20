<?php

namespace Vanguard\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Vanguard\Jobs\AdminAllBackup;
use Vanguard\Jobs\TablesUsagesFixing;
use Vanguard\Jobs\UsersBackups;
use Vanguard\Jobs\UsersDailyPay;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
       //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('inspire')
//                 ->hourly();
        $usersDailyPay = $this->app->make(UsersDailyPay::class);
        $tablesUsagesFixing = $this->app->make(TablesUsagesFixing::class);

        $schedule->job($tablesUsagesFixing)->dailyAt('05:30');
        $schedule->job($usersDailyPay)->dailyAt('06:00');
        $schedule->job(new AdminAllBackup())->dailyAt('06:30');
        $schedule->job(new UsersBackups())->everyMinute();
    }


    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
