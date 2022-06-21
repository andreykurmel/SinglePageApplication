<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vanguard\Models\Table\TableBackup;
use Vanguard\User;

class ClearStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        exec('rm -rf '.storage_path('app/csv'));
        exec('mkdir '.storage_path('app/csv'));
        exec('chmod -R 777 '.storage_path('app/csv'));

        exec('rm -rf '.storage_path('app/tmp_import'));
        exec('mkdir '.storage_path('app/tmp_import'));
        exec('chmod -R 777 '.storage_path('app/tmp_import'));

        exec('rm -rf '.storage_path('app/tmp'));
        exec('mkdir '.storage_path('app/tmp'));
        exec('chmod -R 777 '.storage_path('app/tmp'));

        exec('rm -rf '.storage_path('app/pasted'));
        exec('mkdir '.storage_path('app/pasted'));
        exec('chmod -R 777 '.storage_path('app/pasted'));
    }

    public function failed()
    {
        //
    }
}
