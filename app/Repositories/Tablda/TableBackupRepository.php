<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Services\Tablda\HelperService;

class TableBackupRepository
{
    protected $service;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get only table backup info
     *
     * @param $table_backup_id
     * @return mixed
     */
    public function getTableBackup($table_backup_id)
    {
        return TableBackup::where('id', '=', $table_backup_id)->first();
    }

    /**
     * @param string $day
     * @param string $time
     * @return Collection
     */
    public function getbyTime(string $day, string $time)
    {
        $backups = TableBackup::where(function ($q) use ($day) {
            $q->where('day', '=', $day);
            $q->orWhere('day', '=', 'Daily');
        });

        if ($time == '00:00') {
            $backups->where(function ($q) use ($time) {
                $q->whereNull('time');
                $q->orWhere('time', 'like', $time.':%');
            });
        } else {
            $backups->where('time', 'like', $time.':%');
        }

        return $backups->get();
    }

    /**
     * @param array $data
     * @param string $table_url
     * @return \Illuminate\Database\Eloquent\Model|TableBackup
     */
    public function addTableBackup(array $data, string $table_url)
    {
        $cloud = (new UserCloudRepository())->getCloud($data['user_cloud_id']);
        $table = (new TableRepository())->getTable($data['table_id']);
        $account = auth()->user();
        $user = $cloud->_user;
        $user_email = $user->email . ($account && $account->email != $user->email ? ','.$account->email : '');

        $data['user_id'] = $user->id;
        $data['is_active'] = true;
        $bkp = new TableBackup($data);

        $data['day'] = $data['day'] ?? 'Daily';
        $data['bkp_email_field_static'] = $data['bkp_email_field_static'] ?? $user_email;
        $data['bkp_email_subject'] = $data['bkp_email_subject'] ?? "{$data['day']} backup to {$cloud->name}, {$user->username}/{$table->name}";
        $data['bkp_email_message'] = $data['bkp_email_message'] ?? "Table $table_url has been successfully backed up to folder: {$bkp->getsubfolder(true)}";

        $data['overwrite'] = $data['mysql'] = $data['csv'] = $data['attach'] = 1;
        $data['bkp_email_def'] = $data['bkp_subject_def'] = $data['bkp_addressee_def'] = $data['bkp_message_def'] = 1;

        return TableBackup::create(array_merge($data, $this->service->getCreated(), $this->service->getModified()));
    }

    /**
     * Update Backup row.
     *
     * @param $table_backup_id
     * @param array $data
     * @param string $table_url
     * @return mixed
     */
    public function updateTableBackup($table_backup_id, array $data, string $table_url)
    {
        $data['_changed_field'] = $data['_changed_field'] ?? '';
        if ($data['_changed_field'] == 'bkp_email_field_static') {
            $data['bkp_email_def'] = 0;
        }
        if ($data['_changed_field'] == 'bkp_email_subject') {
            $data['bkp_subject_def'] = 0;
        }
        if ($data['_changed_field'] == 'bkp_addressee_txt') {
            $data['bkp_addressee_def'] = 0;
        }
        if ($data['_changed_field'] == 'bkp_email_message') {
            $data['bkp_message_def'] = 0;
        }

        $bkp = new TableBackup($data);
        $cloud = (new UserCloudRepository())->getCloud($data['user_cloud_id']);
        $table = (new TableRepository())->getTable($data['table_id']);
        $user = $cloud->_user;

        if ($data['bkp_email_def']) {
            $data['bkp_email_field_static'] = $user->email;
        }
        if ($data['bkp_email_def']) {
            $data['bkp_email_subject'] = "{$data['day']} backup to {$cloud->name}, {$user->username}/{$table->name}";
        }
        if ($data['bkp_message_def']) {
            $data['bkp_email_message'] = "Table $table_url has been successfully backed up to folder: {$bkp->getsubfolder(true)}";
        }

        return TableBackup::where('id', '=', $table_backup_id)->update($this->service->delSystemFields($data));
    }

    /**
     * Delete Backup row.
     *
     * @param $table_backup_id
     * @return mixed
     */
    public function deleteTableBackup($table_backup_id)
    {
        return TableBackup::where('id', '=', $table_backup_id)->delete();
    }
}