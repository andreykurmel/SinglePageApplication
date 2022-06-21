<?php

namespace Vanguard\Services\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class TablePermissionService
{
    protected $service;
    protected $permissionRepository;
    protected $tableFieldRepository;
    protected $colGroupRepository;

    /**
     * TablePermissionService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->permissionRepository = new TablePermissionRepository();
        $this->tableFieldRepository = new TableFieldRepository();
        $this->colGroupRepository = new TableColGroupRepository();
    }

    /**
     * Get Permission.
     *
     * @param $table_permission_id
     * @return mixed
     */
    public function getPermission($table_permission_id)
    {
        return $this->permissionRepository->getPermission($table_permission_id);
    }

    /**
     * Change Default Value for Field for provided Table Permission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @param $def_val
     * @return int|mixed
     */
    public function defaultField(Int $table_permission_id, $user_group_id, Int $table_field_id, $def_val) {
        if ($this->permissionRepository->getDefField($table_permission_id, $user_group_id, $table_field_id)) {
            return $this->permissionRepository->updateDefField($table_permission_id, $user_group_id, $table_field_id, $def_val);
        } else {
            return $def_val ?
                $this->permissionRepository->insertDefField([
                    'table_permission_id' => $table_permission_id,
                    'user_group_id' => $user_group_id,
                    'table_field_id' => $table_field_id,
                    'default' => $def_val
                ])
                :
                1;
        }
    }

    /**
     * Add Visitor Permission (needs to be added to each table).
     *
     * @param $table_id - int
     * @param $can_edit - int
     * @param $except_columns - array
     * @return void
     */
    public function addSystemRights($table_id, $can_edit = 0, $except_columns = []) {
        $standard_system_rights = [
            1 => $this->service->getVisitorRightName(),
        ];

        foreach ($standard_system_rights as $type => $right) {
            $this->addSysRight($table_id, $type, $right, $can_edit, $except_columns);
        }
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
        return $this->permissionRepository->getSysPermission($table_id, $type);
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
        $this->updateMenutree($user_group_id);
        return $this->permissionRepository->attachUserGroupPermission($tablePermission, $user_group_id, $active);
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
        $this->updateMenutree($user_group_id);
        return $this->permissionRepository->updateUserGroupPermission($tablePermission, $user_group_id, $active);
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
        $this->updateMenutree($user_group_id);
        return $this->permissionRepository->detachUserGroupPermission($tablePermission, $user_group_id);
    }

    /**
     * @param $user_group_id
     */
    protected function updateMenutree($user_group_id)
    {
        $user_ids = (new UserGroupRepository())->getGroupUsrFields($user_group_id, 'id');
        (new UserRepository())->newMenutreeHash($user_ids);
    }

    /**
     * Add Visitor Permission (needs to be added to each table).
     *
     * @param $table_id - int
     * @param $type - int [1 = Visitor; 2 = ViaFolder]
     * @param $right_name - string
     * @param $can_edit - int
     * @param $except_columns - array
     * @return void
     */
    public function addSysRight($table_id, $type, $right_name, $can_edit = 0, $except_columns = []) {
        if ($this->permissionRepository->getSysPermission($table_id, $type)) {
            return;
        }

        $table_permission = $this->permissionRepository->addPermission([
            'is_system' => $type,
            'table_id' => $table_id,
            'name' => $right_name
        ]);
        $tableFields = $this->tableFieldRepository->getFieldsForTable($table_id);

        $table_col_group = $this->colGroupRepository->addColGroup([
            'is_system' => $type,
            'table_id' => $table_id,
            'name' => $right_name
        ]);
        $this->colGroupRepository->checkAllColFields($table_col_group, $tableFields, $except_columns);

        $this->permissionRepository->updateTableColPermission($table_permission->id, $table_col_group->id, 1, $can_edit);
    }
}