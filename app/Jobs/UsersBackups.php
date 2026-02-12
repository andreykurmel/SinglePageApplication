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

class UsersBackups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userCloudRepo;
    protected $bkpService;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
        $this->userCloudRepo = new UserCloudRepository();
        $this->bkpService = new TableBackupService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(1200);

        (new FileRepository())->fixDDLfiles();

        //get activated backups
        $time = date('H:i', time());
        $day = date('l', time());

        $backups = $this->bkpService->getbyTime($day, $time);
        $backups->load([
            '_cloud',
            '_table',
            '_user',
        ]);
        //-----

        foreach ($backups as $backup) {
            if ($backup->_cloud->gettoken() && $backup->_table) {
                try {
                    if ($backup->is_active) {
                        $this->bkpService->notifyUser($backup);
                        (new CloudBackuper($backup))->sendToCloud();
                    }
                } catch (Exception $e) {
                    Log::channel('jobs')->info('UserBackup Error!');
                    Log::channel('jobs')->info($e->getMessage());
                }
            }
        }

    }

    public function failed()
    {
        //
    }
}
