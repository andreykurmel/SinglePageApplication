<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Vanguard\Modules\CloudBackup\CloudBackuper;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\TableBackupService;

class OldSessionsRemover implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sessionIds = \DB::connection('mysql')
            ->table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereRaw('sessions.last_activity < ('.time().' - (users.auto_logout * 60))')
            ->get(['sessions.id'])
            ->pluck('id')
            ->toArray();

        if ($sessionIds) {
            \DB::connection('mysql')
                ->table('sessions')
                ->whereIn('id', $sessionIds)
                ->delete();
        }
    }

    public function failed()
    {
        //
    }
}
