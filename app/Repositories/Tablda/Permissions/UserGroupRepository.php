<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2User;
use Vanguard\Models\User\UserGroupCondition;
use Vanguard\Models\User\UserGroupLink;
use Vanguard\Models\User\UserGroupSubgroup;
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
     * @param $group_id
     * @param string $fld
     * @return array
     */
    public function getGroupUsrFields($group_id, string $fld = 'email')
    {
        $group = UserGroup::where('id', '=', $group_id)
            ->with('_individuals_all')
            ->first();

        return $group ?
            $group->_individuals_all->pluck($fld)->toArray()
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
     * @param $model_id
     * @return \Illuminate\Database\Eloquent\Model|object|UserGroup|null
     */
    public function getGroupSubgroup($model_id)
    {
        $model = UserGroupSubgroup::where('id', '=', $model_id)->first();
        return UserGroup::where('id', '=', $model->usergroup_id)->first();
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
        $user_group->_subgroups = [];
        $user_group->_tables_shared = [];
        $user_group->_table_permissions = [];
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
                    '_table_permissions',
                    '_subgroups',
                ]);
                $ug->withCount('_individuals_all');
            },
            '_sys_user_groups' => function ($sug) use ($only_names) {
                $sug->with([
                    '_tables_shared',
                ]);
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
        $data['logic_operator'] = $data['logic_operator'] ?? 'AND';
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
     * @param int $user_group_id
     * @param array $data
     * @return mixed
     */
    public function updateAllConditions(int $user_group_id, array $data)
    {
        return UserGroupCondition::where('user_group_id', '=', $user_group_id)
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

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|UserGroupSubgroup
     */
    public function addSubGroup(array $data)
    {
        $subgroup = UserGroupSubgroup::create( $this->service->delSystemFields($data) );

        $this->syncCachedUsers($subgroup->usergroup_id);

        return $subgroup;
    }

    /**
     * @param int $model_id
     * @param array $data
     * @return void
     */
    public function updateSubGroup(int $model_id, array $data)
    {
        $subgroup = UserGroupSubgroup::find($model_id);

        UserGroupSubgroup::where('id', $model_id)
            ->update( $this->service->delSystemFields($data) );

        $this->syncCachedUsers($subgroup->usergroup_id);
    }

    /**
     * @param int $model_id
     * @return void
     * @throws \Exception
     */
    public function deleteSubGroup(int $model_id)
    {
        $subgroup = UserGroupSubgroup::find($model_id);

        UserGroupSubgroup::where('id', $model_id)->delete();

        $this->syncCachedUsers($subgroup->usergroup_id);
    }

    /**
     * @param int $usergroup_id
     * @return void
     * @throws \Exception
     */
    protected function syncCachedUsers(int $usergroup_id)
    {
        $usergroup = UserGroup::find($usergroup_id);
        $subgroups = UserGroup::whereIn('id', $usergroup->_subgroups->pluck('subgroup_id'))
            ->with('_individuals_all')
            ->get();

        $cachedUserIds = $subgroups->pluck('_individuals_all')->flatten()->pluck('id');

        UserGroup2User::where('user_group_id', '=', $usergroup->id)
            ->where('cached_from_conditions', '=', 1)
            ->delete();
        foreach ($cachedUserIds as $userId) {
            UserGroup2User::create([
                'user_group_id' => $usergroup->id,
                'user_id' => $userId,
                'cached_from_conditions' => 1,
            ]);
        }
    }
}