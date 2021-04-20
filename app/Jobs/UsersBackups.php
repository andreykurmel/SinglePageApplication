<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Modules\CloudBackup\CloudBackuper;
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
        set_time_limit(300);

        //get activated backups
        $time = date('H:i', time());
        $day = date('l', time());

        $backups = $this->bkpService->getbyTime($day, $time);
        $backups->load([
            '_cloud' => function ($q) {
                $q->with('_user');
            },
            '_table'
        ]);
        //-----

        foreach ($backups as $backup) {
            if ($backup->_cloud->gettoken() && $backup->_table) {
                try {
                    (new CloudBackuper($backup))->sendToCloud();
                    $this->bkpService->notifyUser($backup);
                } catch (\Exception $e) {
                }
            }
        }

    }

    public function failed()
    {
        //
    }
}
