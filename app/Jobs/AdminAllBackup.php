<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Vanguard\Models\Table\TableBackup;
use Vanguard\User;

class AdminAllBackup implements ShouldQueue
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
        ini_set('max_execution_time', '1200');
        ini_set('memory_limit', '1024M');
        // /usr/bin/mysqldump -u root -f --skip-extended-insert --databases app_correspondence app_data app_sys  > /var/www/full-backup-$(date +\%F).sql
        //backup all DB
        exec('export MYSQL_PWD='.escapeshellarg(env('DB_PASSWORD')).' ; '
                .'/usr/bin/mysqldump -h '.escapeshellarg(env('DB_HOST'))
                .' -u root -f --skip-extended-insert '
                .' --databases '.escapeshellarg(env('DB_DATABASE_CORRESPONDENCE', 'app_correspondence'))
                .' '.escapeshellarg(env('DB_DATABASE_DATA', 'app_data'))
                .' '.escapeshellarg(env('DB_DATABASE', 'app_sys'))
                .' > /var/www/full-backup-$(date +\%F).sql 2>&1');
        //store in DROPBOX
        if (env('BACKUP_TO_DBOX')) {
            exec('echo "OAUTH_ACCESS_TOKEN='.env('DBOX_ADMIN_API').'" > /var/www/.dropbox_uploader');
            exec(env('DBOX_UPLOADER_FILE').' upload /var/www/full-backup-$(date +\%F).sql / 2>&1');
        }
        //remove old backup (3days)
        exec('rm /var/www/full-backup-$(date -d "-3days" +\%F).sql');
    }

    public function failed()
    {
        //
    }
}
