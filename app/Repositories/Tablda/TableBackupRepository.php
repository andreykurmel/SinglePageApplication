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
     * Add Backup row to store Table
     *
     * @param array $data
     * @return mixed
     */
    public function addTableBackup(Array $data)
    {
        return TableBackup::create(array_merge($data, $this->service->getCreated(), $this->service->getModified()));
    }

    /**
     * Update Backup row.
     *
     * @param $table_backup_id
     * @param array $data
     * @return mixed
     */
    public function updateTableBackup($table_backup_id, Array $data)
    {
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