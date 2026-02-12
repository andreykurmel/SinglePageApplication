<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionDefaultField;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\Table\TableStatuse;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class TablePermissionRepository
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
     * @param int $table_id
     * @param int|null $user_id
     * @param int|null $direct_id
     * @param bool $visitor_scope
     * @return \Illuminate\Support\Collection
     */
    public function tablePermissions(int $table_id, int $user_id = null, int $direct_id = null, bool $visitor_scope = false)
    {
        $added = (app(AuthUserSingleton::class))->getManagerOfUserGroups();
        return TablePermission::where('table_id', '=', $table_id)
            ->applyIsActiveForUserOrPermission($direct_id, $visitor_scope)
            //get relations
            ->with([
                '_row_groups',
                '_column_groups' => function ($_cg) {
                    $_cg->with('_fields');
                },
                '_default_fields' => function ($_df) use ($user_id) {
                    $_df->with('_field:id,table_id,field');
                    $_df->hasUserGroupForUser($user_id);
                },
                '_addons',
                '_forbid_settings',
                '_shared_tables' => function ($_ug) use ($added) {
                    $_ug->where('is_active', 1);
                    $_ug->whereIn('user_group_id', $added);
                }
            ])
            ->get();
    }

    /**
     * @param TablePermission $from
     * @param TablePermission $to
     * @return mixed
     */
    public function copyPermission(TablePermission $from, TablePermission $to)
    {
        TableRights::forgetCache($to->table_id);

        $arr = [
            'name' => $to->name,
            'is_system' => 0,
        ];
        $res = TablePermission::where('id', '=', $to->id)->update(array_merge($this->service->delSystemFields($from->toArray()), $arr));

        $to->_addons()->detach();
        foreach ($from->_addons as $elem) {
            $to->_addons()->attach($elem->id, [
                'type' => $elem->_link->type,
            ]);
        }

        $to->_column_groups()->detach();
        foreach ($from->_column_groups as $elem) {
            $to->_column_groups()->attach($elem->id, [
                'view' => $elem->_link->view,
                'edit' => $elem->_link->edit,
                'delete' => $elem->_link->delete,
                'shared' => $elem->_link->shared,
            ]);
        }

        $to->_row_groups()->detach();
        foreach ($from->_row_groups as $elem) {
            $to->_row_groups()->attach($elem->id, [
                'view' => $elem->_link->view,
                'edit' => $elem->_link->edit,
                'delete' => $elem->_link->delete,
                'shared' => $elem->_link->shared,
            ]);
        }

        $to->_cond_formats()->detach();
        foreach ($from->_cond_formats as $elem) {
            $to->_cond_formats()->attach($elem->id, [
                'always_on' => $elem->_pivot->always_on,
                'visible_shared' => $elem->_pivot->visible_shared,
            ]);
        }

        $to->_charts()->detach();
        foreach ($from->_charts as $elem) {
            $to->_charts()->attach($elem->id, [
                'can_edit' => $elem->_pivot->can_edit,
            ]);
        }

        $to->_views()->detach();
        foreach ($from->_views as $elem) {
            $to->_views()->attach($elem->id);
        }

        $to->_forbid_settings()->delete();
        foreach ($from->_forbid_settings as $elem) {
            $to->_forbid_settings()->insert(array_merge(
                $this->service->delSystemFields($elem->toArray()),
                ['permission_id' => $to->id]
            ));
        }

        $to->_link_limits()->delete();
        foreach ($from->_link_limits as $elem) {
            $to->_link_limits()->insert(array_merge(
                $this->service->delSystemFields($elem->toArray()),
                ['table_permission_id' => $to->id]
            ));
        }

        return $res;
    }

    /**
     * Get Visitor Permission.
     *
     * @param $table_id
     * @param int $type - [1 = Visitor; 2 = ViaFolder]
     * @return mixed
     */
    public function getSysPermission($table_id, int $type)
    {
        return TablePermission::where('table_id', '=', $table_id)
            ->where('is_system', '=', $type)
            ->first();
    }

    /**
     * Add Permission.
     *
     * @param $data
     * [
     *  +user_group_id: int,
     *  +table_id: int,
     * ]
     * @return mixed
     */
    public function addPermission($data)
    {
        TableRights::forgetCache($data['table_id']);

        if (!empty($data['user_group_id'])) {
            $ug = UserGroup::where('id', $data['user_group_id'])->first();
            $ug->_tables()->attach($data['table_id']);
        }

        $created = TablePermission::create($this->service->delSystemFields($data));
        return $this->loadPermisWithRelations($created->table_id, $created->id)->first();
    }

    /**
     * @param int $table_id
     * @param int|null $permis_id
     * @return Builder
     */
    public function loadPermisWithRelations(int $table_id, int $permis_id = null)
    {
        $sql = TablePermission::where('table_id', '=', $table_id);
        if ($permis_id) {
            $sql->where('id', '=', $permis_id);
        }
        $sql->with([
            '_permission_columns',
            '_permission_rows',
            '_user_groups',
            '_default_fields',
            '_addons',
            '_forbid_settings',
            '_link_limits',
        ]);
        return $sql;
    }

    /**
     * @param TablePermission $permission
     * @param $data
     * @return bool
     */
    public function updatePermission(TablePermission $permission, $data)
    {
        TableRights::forgetCache($permission->table_id);

        return TablePermission::where('id', $permission->id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param TablePermission $permission
     * @param $table_id
     * @return bool|null
     * @throws Exception
     */
    public function deletePermission(TablePermission $permission, $table_id)
    {
        TableRights::forgetCache($permission->table_id);

        return TablePermission::where('id', $permission->id)->delete();
    }

    /**
     * Update or Create Table Column Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $col_group_id
     * @param int $view
     * @param int $edit
     * @param int $shared
     * @return TablePermissionColumn
     * @throws Exception
     */
    public function updateTableColPermission($table_permission_id, $col_group_id, $view = 0, $edit = 0, $shared = 0): TablePermissionColumn
    {
        $permission = $this->getPermission($table_permission_id);
        TableRights::forgetCache($permission->table_id);

        $this->clearStatusForNewColumns($table_permission_id);

        $permisCol = TablePermissionColumn::where('table_permission_id', $table_permission_id)
            ->where('table_column_group_id', $col_group_id)
            ->first();

        if (!$permisCol) {
            $permisCol = TablePermissionColumn::create([
                'table_permission_id' => $table_permission_id,
                'table_column_group_id' => $col_group_id
            ]);
        }

        $permisCol->update(['view' => $view, 'edit' => $edit, 'shared' => $shared]);

        return $permisCol;
    }

    /**
     * Get Permission.
     *
     * @param $table_permission_id
     * @return null|TablePermission
     */
    public function getPermission($table_permission_id)
    {
        return TablePermission::where('id', '=', $table_permission_id)->first();
    }

    /**
     * @param $table_permission_id
     * @throws Exception
     */
    protected function clearStatusForNewColumns($table_permission_id)
    {
        $tp = TablePermission::where('id', $table_permission_id)
            ->with([
                '_user_groups' => function ($ug) {
                    $ug->with([
                        '_individuals_all' => function ($ia) {
                            $ia->select('users.id');
                        }
                    ]);
                    $ug->select('user_groups.id');
                }
            ])
            ->first();

        $user_ids = $tp->_user_groups ? $tp->_user_groups->pluck('_individuals_all') : null;
        $user_ids = $user_ids ? $user_ids->flatten()->pluck('id')->toArray() : null;

        TableStatuse::where('table_id', $tp->table_id)
            ->whereIn('user_id', $user_ids)
            ->delete();
    }

    /**
     * Get link to Table Row Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $row_group_id
     * @return TablePermissionRow
     */
    public function getTableRowInPermission($table_permission_id, $row_group_id)
    {
        return TablePermissionRow::where('table_permission_id', $table_permission_id)
            ->where('table_row_group_id', $row_group_id)
            ->first();
    }

    /**
     * Attach User Group to Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @param $active
     * @return bool
     */
    public function attachUserGroupPermission(TablePermission $tablePermission, $user_group_id, $active)
    {
        TableRights::forgetCache($tablePermission->table_id);

        $tablePermission->_user_groups()->attach($user_group_id, [
            'is_active' => $active ? 1 : 0,
            'table_id' => $tablePermission->table_id,
        ]);

        return UserGroup2TablePermission::where('table_permission_id', $tablePermission->id)
            ->where('user_group_id', $user_group_id)
            ->first();
    }

    /**
     * Update User Group in Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @param $active
     * @return bool
     */
    public function updateUserGroupPermission(TablePermission $tablePermission, $user_group_id, $active)
    {
        TableRights::forgetCache($tablePermission->table_id);

        if ($active && $tablePermission->active != 1) {
            $tablePermission->update(['active' => 1]);
        }

        return UserGroup2TablePermission::where('table_permission_id', $tablePermission->id)
            ->where('user_group_id', $user_group_id)
            ->update(['is_active' => $active ? 1 : 0]);
    }

    /**
     * Detach User Group to Table Permission.
     *
     * @param TablePermission $tablePermission
     * @param $user_group_id
     * @return bool
     */
    public function detachUserGroupPermission(TablePermission $tablePermission, $user_group_id)
    {
        TableRights::forgetCache($tablePermission->table_id);

        return $tablePermission->_user_groups()->detach($user_group_id);
    }

    /**
     * Update or Create Table Row Permission in Table Permission.
     *
     * @param $table_permission_id
     * @param $row_group_id
     * @param $view
     * @param $edit
     * @param $del
     * @param $shared
     * @return TablePermissionRow
     */
    public function updateTableRowPermission($table_permission_id, $row_group_id, $view = 0, $edit = 0, $del = 0, $shared = 0): TablePermissionRow
    {
        $permission = $this->getPermission($table_permission_id);
        TableRights::forgetCache($permission->table_id);

        $permisRow = TablePermissionRow::where('table_permission_id', $table_permission_id)
            ->where('table_row_group_id', $row_group_id)
            ->first();

        if (!$permisRow) {
            $permisRow = TablePermissionRow::create([
                'table_permission_id' => $table_permission_id,
                'table_row_group_id' => $row_group_id
            ]);
        }

        $permisRow->update(['view' => $view, 'edit' => $edit, 'delete' => $del, 'shared' => $shared]);

        return $permisRow;
    }

    /**
     * Get Default Field for provided TablePermission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @return mixed
     */
    public function getDefField(int $table_permission_id, $user_group_id, int $table_field_id)
    {
        return TablePermissionDefaultField::where('table_permission_id', '=', $table_permission_id)
            ->where('user_group_id', '=', $user_group_id)
            ->where('table_field_id', '=', $table_field_id)
            ->first();
    }

    /**
     * Insert Default Field for provided TablePermission.
     *
     * @param $data
     * [
     *  +$table_permission_id: int,
     *  +$table_field_id: int,
     *  +default: string,
     * ]
     * @return mixed
     */
    public function insertDefField($data)
    {
        $permission = $this->getPermission($data['table_permission_id']);
        TableRights::forgetCache($permission->table_id);
        return TablePermissionDefaultField::create($this->service->delSystemFields($data));
    }

    /**
     * Update Default Field for provided TablePermission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @param $default
     * @return mixed
     */
    public function updateDefField(int $table_permission_id, $user_group_id, int $table_field_id, $default)
    {
        $permission = $this->getPermission($table_permission_id);
        TableRights::forgetCache($permission->table_id);

        return TablePermissionDefaultField::where('table_permission_id', '=', $table_permission_id)
            ->where('user_group_id', '=', $user_group_id)
            ->where('table_field_id', '=', $table_field_id)
            ->update(['default' => $default]);
    }

    /**
     * Insert Addon Right to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param $addon_id
     * @param $type
     * @return Collection
     */
    public function insertAddonRight(TablePermission $tablePermission, $addon_id, $type)
    {
        TableRights::forgetCache($tablePermission->table_id);

        if (
            !$tablePermission->_addons()
                ->where('addons.id', $addon_id)
                ->where('table_permissions_2_addons.type', $type)
                ->count()
        ) {
            $tablePermission->_addons()->attach($addon_id, ['type' => $type]);
        }
        return $tablePermission->_addons()->get();
    }

    /**
     * @param TablePermission $tablePermission
     * @param int $addon_id
     * @param string $fld
     * @param $val
     * @return mixed
     */
    public function updateAddonRight(TablePermission $tablePermission, int $addon_id, string $fld, $val)
    {
        TableRights::forgetCache($tablePermission->table_id);

        DB::table('table_permissions_2_addons')
            ->where('table_permission_id', $tablePermission->id)
            ->where('addon_id', $addon_id)
            ->update([$fld => $val]);

        return $tablePermission->_addons()->get();
    }

    /**
     * Delete Addon Right to TablePermission.
     *
     * @param TablePermission $tablePermission
     * @param $addon_id
     * @return Collection
     */
    public function deleteAddonRight(TablePermission $tablePermission, $addon_id, $type)
    {
        TableRights::forgetCache($tablePermission->table_id);

        DB::table('table_permissions_2_addons')
            ->where('table_permission_id', $tablePermission->id)
            ->where('addon_id', $addon_id)
            ->where('type', $type)
            ->delete();

        return $tablePermission->_addons()->get();
    }

    /**
     * @param int $table_id
     * @param $user_id
     * @return mixed
     */
    public function canReference(int $table_id, int $user_id = null)
    {
        if ($user_id) {
            //for selected user
            return TablePermission::where('table_id', $table_id)
                ->where('can_reference', 1)
                ->isActiveForSelectedUser($user_id)
                ->count();
        } else {
            //for current user
            return TablePermission::where('table_id', $table_id)
                ->where('can_reference', 1)
                ->isActiveForUserOrVisitor()
                ->count();
        }
    }
}