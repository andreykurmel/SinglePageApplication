<?php

namespace Vanguard\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Vanguard\Console\Commands\BackupAllCommand;
use Vanguard\Console\Commands\CreateDatabaseCommand;
use Vanguard\Jobs\AdminAllBackup;
use Vanguard\Jobs\ClearStorage;
use Vanguard\Jobs\TablesUsagesFixing;
use Vanguard\Jobs\UserNotifyConfirm;
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
        CreateDatabaseCommand::class,
        BackupAllCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (env('APP_DEBUG')) {
            return;
        }
//        $schedule->command('inspire')
//                 ->hourly();
        $usersDailyPay = $this->app->make(UsersDailyPay::class);
        $tablesUsagesFixing = $this->app->make(TablesUsagesFixing::class);

        $schedule->job(new ClearStorage())->dailyAt('04:20');
        $schedule->job($tablesUsagesFixing)->dailyAt('04:30');
        $schedule->job($usersDailyPay)->dailyAt('05:00');
        $schedule->job(new AdminAllBackup())->dailyAt('05:30');
        $schedule->job(new UsersBackups())->everyMinute();
        $schedule->job(new UserNotifyConfirm())->dailyAt('12:00');
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
