<?php


namespace Vanguard\Services\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
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
        if ($userGroup->_conditions()->count()+1 > 2) { //+1 - because 'system' is virtual
            $this->userGroupRepository->updateAllConditions($userGroup->id, ['logic_operator' => 'AND']);
        }
        return $userGroup->_conditions()->get();
    }

    /**
     * Cache Users from Conditions.
     *
     * @param UserGroup $userGroup
     * @param bool $force
     */
    private function cacheUsersFromConditions(UserGroup $userGroup, bool $force = false)
    {
        if (count($userGroup->_conditions) || $force) {
            $sql = User::query()->where('email', 'LIKE', '%@' . HelperService::usrEmailDomain());;
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
     * @param UserGroup $userGroup
     * @param array $data
     * @return Collection
     */
    public function addSubgroup(UserGroup $userGroup, array $data)
    {
        $this->userGroupRepository->addSubGroup(array_merge($data, ['usergroup_id' => $userGroup->id]));
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $userGroup->_subgroups()->get();
    }

    /**
     * @param UserGroup $userGroup
     * @param $model_id
     * @param $data
     * @return Collection
     */
    public function updateSubgroup(UserGroup $userGroup, $model_id, $data)
    {
        $this->userGroupRepository->updateSubGroup($model_id, $data);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $userGroup->_subgroups()->get();
    }

    /**
     * @param UserGroup $userGroup
     * @param $model_id
     * @return Collection
     * @throws \Exception
     */
    public function deleteSubgroup(UserGroup $userGroup, $model_id)
    {
        $this->userGroupRepository->deleteSubGroup($model_id);
        $this->updateCollaboratorsForTables($userGroup->_tables);
        return $userGroup->_subgroups()->get();
    }

    /**
     * Transfer Conditions to Individuals
     *
     * @param UserGroup $userGroup
     * @return mixed
     */
    public function changeCondToUsers(UserGroup $userGroup)
    {
        $this->cacheUsersFromConditions($userGroup, true);
        return $this->userGroupRepository->changeCondToUsers($userGroup);
    }
}