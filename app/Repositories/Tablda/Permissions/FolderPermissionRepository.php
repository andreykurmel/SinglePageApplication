<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\Services\Tablda\HelperService;

class FolderPermissionRepository
{
    protected $service;

    /**
     * FolderPermissionRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Permission.
     *
     * @param $user_group_id
     * @return UserGroup|null
     */
    public function getUserGroup($user_group_id)
    {
        return UserGroup::where('id', '=', $user_group_id)->first();
    }

    /**
     * @param UserGroup $user_group
     * @param int $is_active
     * @param int $is_app
     * @param array $old_tables
     * @param array $checked_tables
     */
    public function updatePermissions(UserGroup $user_group, int $is_active, int $is_app, array $old_tables, array $checked_tables)
    {
        $old_tables = collect($old_tables)->unique();
        $checked_tables = collect($checked_tables)->unique();

        //delete unchecked tables from UserGroup
        $to_del = $old_tables->diff($checked_tables);
        if ($to_del->count()) {
            $user_group->_tables_shared()
                ->whereIn('table_id', $to_del)
                ->delete();
        }

        //add checked tables for UserGroup
        $to_add = collect([]);
        foreach ($checked_tables->diff($old_tables) as $new_tb) {
            $to_add[] = [
                'user_group_id' => $user_group->id,
                'table_id' => $new_tb,
            ];
        }
        if ($to_add->count()) {
            $user_group->_tables_shared()->insert( $to_add->toArray() );
        }

        //update Is_App and Is_Active for all checked tables
        $user_group->_tables_shared()
            ->whereIn('table_id', $checked_tables)
            ->update([
                'is_app' => $is_app,
                'is_active' => $is_active,
            ]);
    }

    /**
     * @param UserGroup $user_group
     * @param int $is_active
     * @param int $is_app
     * @param array $checked_tables
     */
    public function updateSystemPermissions(UserGroup $user_group, int $is_active, int $is_app, array $checked_tables)
    {
        //delete unchecked tables from UserGroup
        $user_group->_tables_shared()->delete();

        //add checked tables for UserGroup
        $to_add = [];
        foreach ($checked_tables as $new_tb) {
            $to_add[] = [
                'user_group_id' => $user_group->id,
                'table_id' => $new_tb,
                'is_app' => $is_app,
                'is_active' => $is_active,
            ];
        }
        if ($to_add) {
            $user_group->_tables_shared()->insert($to_add);
        }
    }

    /**
     * @param UserGroup $user_group
     * @param int $tb_shared_id
     * @param int $permission_id
     * @return void
     */
    public function assignPermission(UserGroup $user_group, int $tb_shared_id, int $permission_id)
    {
        $user_group->_tables_shared()
            ->where('id', '=', $tb_shared_id)
            ->update(['table_permission_id' => $permission_id]);
    }
}