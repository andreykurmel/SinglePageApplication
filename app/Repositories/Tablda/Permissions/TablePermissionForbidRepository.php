<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Services\Tablda\HelperService;

class TablePermissionForbidRepository
{
    protected $service;

    /**
     * TablePermissionRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * addForbidSetting
     *
     * @param int $permission_id
     * @param string $db_col_name
     * @return TablePermissionForbidSettings
     */
    public function addForbidSetting(int $permission_id, string $db_col_name)
    {
        return TablePermissionForbidSettings::create([
            'permission_id' => $permission_id,
            'db_col_name' => $db_col_name,
        ]);
    }

    /**
     * addAllForbidSetting
     *
     * @param int $permission_id
     */
    public function addAllForbidSetting(int $permission_id)
    {
        $this->deleteAllForbidSetting($permission_id);
        foreach ((new UserHeaders())->avail_override_fields as $db_name) {
            TablePermissionForbidSettings::create([
                'permission_id' => $permission_id,
                'db_col_name' => $db_name,
            ]);
        }
    }

    /**
     * deleteForbidSetting
     *
     * @param int $permission_id
     * @param string $db_col_name
     * @return int
     */
    public function deleteForbidSetting(int $permission_id, string $db_col_name)
    {
        return TablePermissionForbidSettings::where('permission_id', $permission_id)
            ->where('db_col_name', $db_col_name)
            ->delete();
    }

    /**
     * deleteAllForbidSetting
     *
     * @param int $permission_id
     * @return int
     */
    public function deleteAllForbidSetting(int $permission_id)
    {
        return TablePermissionForbidSettings::where('permission_id', $permission_id)->delete();
    }
}