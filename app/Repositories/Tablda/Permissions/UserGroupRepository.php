<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\User\UserGroupCondition;
use Vanguard\Models\DataSetPermissions\TablePermissionDefaultField;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\User\UserGroupLink;
use Vanguard\Models\Table\Table;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class UserGroupRepository
{
    protected $service;

    /**
     * UserGroupRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Group.
     *
     * @param $group_id
     * @return mixed
     */
    public function getGroup($group_id)
    {
        return UserGroup::where('id', '=', $group_id)->first();
    }

    /**
     * Get Group.
     *
     * @param $group_id
     * @return array
     */
    public function getGroupEmails($group_id)
    {
        $group = UserGroup::where('id', '=', $group_id)
            ->with('_individuals_all')
            ->first();

        return $group ?
            $group->_individuals_all->pluck('email')->toArray()
            : [];
    }

    /**
     * Get Group by Condition ID.
     *
     * @param $group_condition_id
     * @return mixed
     */
    public function getGroupByCondId($group_condition_id)
    {
        $cond = UserGroupCondition::where('id', '=', $group_condition_id)->first();
        return UserGroup::where('id', '=', $cond->user_group_id)->first();
    }

    /**
     * Add Group.
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
        $created = UserGroup::create( $this->service->delSystemFields($data) );
        $user_group = $this->getGroup($created->id);
        $user_group->_individuals = [];
        $user_group->_conditions = [];
        return $user_group;
    }

    /**
     * Update Group
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
        return UserGroup::where('id', $group_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Group
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteGroup($group_id)
    {
        return UserGroup::where('id', $group_id)->delete();
    }

    /**
     * Add User To Group
     *
     * @param \Vanguard\Models\User\UserGroup $userGroup
     * @param int $user_id
     * @return mixed
     */
    public function addUserToGroup(UserGroup $userGroup, $user_id)
    {
        $userGroup->_individuals()->attach($user_id);
        return UserGroupLink::where('user_group_id', $userGroup->id)
            ->where('user_id', $user_id)
            ->first();
    }

    /**
     * @param User $user
     * @return User
     */
    public function loadUserGroups(User $user)
    {
        $only_names = $this->service->onlyNames();
        return $user->load([
            '_user_groups' => function ($ug) use ($only_names) {
                $ug->with([
                    '_individuals:'.$only_names,
                    '_conditions',
                    '_tables_shared',
                    '_table_permissions'
                ]);
                $ug->withCount('_individuals_all');
            },
        ]);
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
        return UserGroupLink::where('user_group_id', $userGroup->id)
            ->where('user_id', $user_id)
            ->update(['is_edit_added' => $is_edit_added ? 1 : 0]);
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
        return $userGroup->_individuals()->detach($user_id);
    }

    /**
     * Add Group Condition
     *
     * @param $data
     * [
     *  +table_row_group_id: int,
     *  +table_field_id: int,
     *  +compared_operator: string,
     *  +compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return mixed
     */
    public function addGroupCondition($data)
    {
        return UserGroupCondition::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Group Condition
     *
     * @param int $condition_id
     * @param $data
     * [
     *  -table_row_group_id: int,
     *  -table_field_id: int,
     *  -compared_operator: string,
     *  -compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return array
     */
    public function updateGroupCondition($condition_id, $data)
    {
        return UserGroupCondition::where('id', $condition_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Group Condition
     *
     * @param int $condition_id
     * @return mixed
     */
    public function deleteGroupCondition($condition_id)
    {
        return UserGroupCondition::where('id', $condition_id)->delete();
    }

    /**
     * Transfer Conditions to Individuals
     *
     * @param UserGroup $userGroup
     * @return mixed
     */
    public function changeCondToUsers(UserGroup $userGroup) {
        //Cached Users to Individuals
        $userGroup->_links()->update(['cached_from_conditions' => 0]);
        //Clear Conditions
        UserGroupCondition::where('user_group_id', $userGroup->id)->delete();

        return $userGroup->_individuals;
    }

    /**
     * Delete Cached Users
     *
     * @param UserGroup $userGroup
     * @return mixed
     */
    public function deleteCachedUsers(UserGroup $userGroup) {
        return UserGroupLink::where('user_group_id', $userGroup->id)
            ->where('cached_from_conditions', 1)
            ->delete();
    }

    /**
     * Add Cached Users
     *
     * @param \Vanguard\Models\User\UserGroup $userGroup
     * @param Collection $users
     * @return mixed
     */
    public function addCachedUsers(UserGroup $userGroup, Collection $users) {
        if (count($users)) {
            $present_users_ids = UserGroupLink::where('user_group_id', $userGroup->id)
                ->select('user_id')
                ->get()
                ->pluck('user_id')
                ->toArray();

            $users = $users->filter(function ($user, $key) use ($present_users_ids) {
                return !in_array($user->field, $present_users_ids);
            });
            $links = $users->map(function ($user, $key) use ($userGroup) {
                return [
                    'user_group_id' => $userGroup->id,
                    'user_id' => $user->id,
                    'cached_from_conditions' => 1
                ];
            });

            return $userGroup->_links()->insert( $links->toArray() );
        }
        return 1;
    }
}