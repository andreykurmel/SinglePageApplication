<?php


namespace Vanguard\Services\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\User;

class UserPermissionsService
{
    protected static $load_permission_cache = [];
    protected $userGroupRepository;
    protected $tableFieldRepository;
    protected $colGroupRepository;
    protected $rowGroupRepository;
    protected $service;

    /**
     * UserPermissionsService constructor.
     */
    public function __construct()
    {
        $this->userGroupRepository = new UserGroupRepository();
        $this->tableFieldRepository = new TableFieldRepository();
        $this->colGroupRepository = new TableColGroupRepository();
        $this->rowGroupRepository = new TableRowGroupRepository();
        $this->service = new HelperService();
    }

    /**
     * Add UserGroup.
     *
     * @param $data
     * [
     *  +user_id: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addGroup($data)
    {
        return $this->userGroupRepository->addGroup($data);
    }

    /**
     * Update UserGroup
     *
     * @param int $group_id
     * @param $data
     * [
     *  -user_id: int,
     *  -name: string,
     *  -notes: string,
     * ]
     * @return array
     */
    public function updateGroup($group_id, $data)
    {
        return $this->userGroupRepository->updateGroup($group_id, $data);
    }

    /**
     * Delete UserGroup.
     *
     * @param UserGroup $userGroup
     * @return mixed
     */
    public function deleteGroup(UserGroup $userGroup)
    {
        $tables = $userGroup->_tables;
        $res = $this->userGroupRepository->deleteGroup($userGroup->id);
        $this->updateCollaboratorsForTables($tables);
        return $res;
    }

    /**
     * Update Collaborators For Table.
     *
     * @param Collection $tables
     * @return int
     */
    public function updateCollaboratorsForTables(Collection $tables)
    {
        $tables->load('_public_links');
        foreach ($tables as $table) {
            $table->num_collaborators = $this->getUsersCountForTable($table->id);
            $table->usage_type = ($table->_public_links->count() ? 'Public' : ($table->num_collaborators > 1 ? 'Semi-Private' : 'Private'));
            $table->save();
        }
        return 1;
    }

    /**
     * Get Users Count For Table.
     *
     * @param int $table_id
     * @return int
     */
    public function getUsersCountForTable(int $table_id)
    {
        return User::whereHas('_member_of_groups', function ($ug) use ($table_id) {
            $ug->whereHas('_tables', function ($t) use ($table_id) {
                $t->where('tables.id', $table_id);
            });
        })
            ->orWhereHas('_tables', function ($t) use ($table_id) {
                $t->where('tables.id', $table_id);
            })
            ->count();
    }

    /**
     * Add User To Group
     *
     * @param UserGroup $userGroup
     * @param int $user_id
     * @return mixed
     */
    public function addUserToGroup(UserGroup $userGroup, $user_id)
    {
        $res = $this->userGroupRepository->addUserToGroup($userGroup, $user_id);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $res;
    }

    /**
     * Update User In Group
     *
     * @param UserGroup $userGroup
     * @param int $user_id
     * @param bool $is_edit_added
     * @return mixed
     */
    public function updateUserInGroup(UserGroup $userGroup, int $user_id, bool $is_edit_added)
    {
        return $this->userGroupRepository->updateUserInGroup($userGroup, $user_id, $is_edit_added);
    }

    /**
     * Delete User From Group
     *
     * @param UserGroup $userGroup
     * @param int $user_id
     * @return mixed
     */
    public function deleteUserFromGroup(UserGroup $userGroup, $user_id)
    {
        $res = $this->userGroupRepository->deleteUserFromGroup($userGroup, $user_id);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $res;
    }

    /**
     * Add Group Condition
     *
     * @param UserGroup $userGroup
     * @param $data
     * [
     *  +user_group_id: int,
     *  +user_field: int,
     *  +compared_operator: string,
     *  +compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return mixed
     */
    public function addGroupCondition(UserGroup $userGroup, array $data)
    {
        $res = $this->userGroupRepository->addGroupCondition(array_merge($data, ['user_group_id' => $userGroup->id]));
        $this->cacheUsersFromConditions($userGroup);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $res;
    }

    /**
     * Cache Users fron Conditions.
     *
     * @param UserGroup $userGroup
     */
    private function cacheUsersFromConditions(UserGroup $userGroup)
    {
        if (count($userGroup->_conditions)) {
            $sql = User::query();
            foreach ($userGroup->_conditions as $condition) {
                if ($condition->logic_operator === 'OR') {
                    $sql->orWhere(
                        $condition->user_field,
                        ($condition->compared_operator == 'Include' ? 'Like' : ($condition->compared_operator ? $condition->compared_operator : '=')),
                        ($condition->compared_operator == 'Include' ? '%' . $condition->compared_value . '%' : $condition->compared_value)
                    );
                } else {
                    $sql->where(
                        $condition->user_field,
                        ($condition->compared_operator == 'Include' ? 'Like' : ($condition->compared_operator ? $condition->compared_operator : '=')),
                        ($condition->compared_operator == 'Include' ? '%' . $condition->compared_value . '%' : $condition->compared_value)
                    );
                }
            }
            $this->userGroupRepository->deleteCachedUsers($userGroup);
            $this->userGroupRepository->addCachedUsers($userGroup, $sql->get());
        }
    }

    /**
     * Update Group Condition
     *
     * @param UserGroup $userGroup
     * @param int $condition_id
     * @param $data
     * [
     *  -user_group_id: int,
     *  -user_field: int,
     *  -compared_operator: string,
     *  -compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return array
     */
    public function updateGroupCondition(UserGroup $userGroup, $condition_id, $data)
    {
        $res = $this->userGroupRepository->updateGroupCondition($condition_id, $data);
        $this->cacheUsersFromConditions($userGroup);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $res;
    }

    /**
     * Delete Group Condition
     *
     * @param UserGroup $userGroup
     * @param int $condition_id
     * @return mixed
     */
    public function deleteGroupCondition(UserGroup $userGroup, $condition_id)
    {
        $res = $this->userGroupRepository->deleteGroupCondition($condition_id);
        $this->cacheUsersFromConditions($userGroup);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $res;
    }

    /**
     * Transfer Conditions to Individuals
     *
     * @param UserGroup $userGroup
     * @return mixed
     */
    public function changeCondToUsers(UserGroup $userGroup)
    {
        return $this->userGroupRepository->changeCondToUsers($userGroup);
    }

    /**
     * Get permissions on the table for user (all permissions through all user groups).
     * Or permissions for selected TablePermission if user works as a guest.
     *
     * @param $user_id
     * @param \Vanguard\Models\Table\Table $table
     * @param $table_permission_id
     * @return object:
     * {
     *  'can_add': int
     *  'can_delete': int
     *  'can_download': string
     *  'view_fields': Collection ['field1', 'field5', ...]
     *  'edit_fields': Collection ['field3', 'field5', ...]
     *  'view_row_groups': Collection [TableRowGroup1, TableRowGroup5, ...]
     *  'edit_row_groups': Collection [TableRowGroup3, TableRowGroup5, ...]
     *  'delete_row_groups': Collection [TableRowGroup3, TableRowGroup5, ...]
     *  'default_values': Collection [
     *      'field1' => 'default1',
     *      ...
     *  ],
     *  '_addons'
     * }
     */
    public function getPermissionsForUser($user_id, Table $table, $table_permission_id = null)
    {
        $tb_permissions = $this->loadTablePermissions($table, $user_id, true, $table_permission_id);

        $permissions = $this->getPermissionsInUserGroups($tb_permissions);

        //user can view all columns if it is View without Permission.
        if ($table_permission_id == -1) {
            $permissions->view_fields = $permissions->view_fields->merge($table->_fields->pluck('field'));
        }

        $permissions->view_fields = $permissions->view_fields->unique()->values();

        return $permissions;
    }

    /**
     * Load Table Permissions.
     *
     * @param Table $table
     * @param $user_id
     * @param bool $all_relations
     * @param null $table_permission_id
     * @return mixed
     */
    public function loadTablePermissions(Table $table, $user_id, $all_relations = true, $table_permission_id = null)
    {
        if ($permis = $this->permisFromCache($table, $all_relations)) {
            return $permis;
        }
        
        $visitor_scope = $user_id ? $this->service->use_visitor_scope : true;

        //Correspondence tables
        if ($table->is_system == 2) {
            $visitor_scope = true;
        }

        if ($all_relations) {
            $added = (app(AuthUserSingleton::class))->getIdsUserGroupsEditAdded();
            $permis = $table->_table_permissions()
                ->applyIsActiveForUserOrPermission($table_permission_id, $visitor_scope)
                //get relations
                ->with([
                    '_row_groups' => function ($_rg) {
                        $_rg->with('_is_group_ref_condition');
                    },
                    '_column_groups' => function ($_cg) {
                        $_cg->with('_fields');
                    },
                    '_default_fields' => function ($_df) use ($user_id) {
                        $_df->with('_field');
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
        } else {
            $permis = $table->_table_permissions()
                ->applyIsActiveForUserOrPermission($table_permission_id, $visitor_scope)
                //get relations
                ->with([
                    '_default_fields' => function ($_df) use ($user_id) {
                        $_df->with('_field');
                        $_df->hasUserGroupForUser($user_id);
                    }
                ])
                ->get();
        }

        $this->permisToCache($permis, $table, $all_relations);
        
        return $permis;
    }

    /**
     * Get permissions on the table in User Groups.
     *
     * @param Collection $_table_permissions
     * @return object
     */
    private function getPermissionsInUserGroups(Collection $_table_permissions)
    {
        $permissions = new CustomPermission();

        foreach ($_table_permissions as $_permission) {
            $permissions->setTablePermis($_permission);
        }

        $permissions->clearNotUniques();

        return $permissions;
    }

    /**
     * Get Array of available columns: ['field1', 'field22', ...]
     *
     * @param $table
     * @param $user_id
     * @param $type ['view', 'edit']
     * @param $table_permission_id
     * @return mixed
     */
    public function getAvailableColumnsArr(Table $table, $user_id, $type = 'view', $table_permission_id = null)
    {
        $fields = $this->getAvailableColumns($table, $user_id, $type, $table_permission_id);
        return $fields->pluck('field')->toArray();
    }

    /**
     * Get available Table Columns: ['field1', 'field22', ...]
     *
     * @param $table
     * @param $user_id
     * @param $type ['view', 'edit']
     * @param $table_permission_id
     * @return mixed
     */
    public function getAvailableColumns(Table $table, $user_id, $type = 'view', $table_permission_id = null)
    {
        return $this->tableFieldRepository->loadFieldsWithPermissions($table, $user_id, $type, $table_permission_id);
    }

    /**
     * @param Table $table
     * @param bool $all_relations
     * @return mixed|null
     */
    private function permisFromCache(Table $table, $all_relations = true)
    {
        $key = $table->db_name . ($all_relations ? '_1' : '_0');
        return self::$load_permission_cache[$key] ?? null;
    }

    /**
     * @param $data
     * @param Table $table
     * @param bool $all_relations
     */
    private function permisToCache($data, Table $table, $all_relations = true)
    {
        $key = $table->db_name . ($all_relations ? '_1' : '_0');
        self::$load_permission_cache[$key] = $data;
    }
}