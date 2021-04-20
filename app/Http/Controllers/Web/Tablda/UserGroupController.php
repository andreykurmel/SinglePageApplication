<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupAddConditionRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupAddRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupChangeRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupDeleteConditionRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupDeleteRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroups2UsersRequest;
use Vanguard\Http\Requests\Tablda\UserGroup\UserGroupUpdateConditionRequest;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\User;

class UserGroupController extends Controller
{
    private $userGroupRepository;
    private $userGroupService;
    private $userRepository;

    /**
     * UserGroupController constructor.
     *
     * @param UserGroupRepository $userGroupRepository
     * @param UserPermissionsService $userGroupService
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserGroupRepository $userGroupRepository,
        UserPermissionsService $userGroupService,
        UserRepository $userRepository
    )
    {
        $this->userGroupRepository = $userGroupRepository;
        $this->userGroupService = $userGroupService;
        $this->userRepository = $userRepository;
    }

    /**
     * Add User Group
     *
     * @param UserGroupAddRequest $request
     * @return mixed
     */
    public function insertUserGroup(UserGroupAddRequest $request){
        return $this->userGroupRepository->addGroup([
            'name' => $request->name,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Update User Group
     *
     * @param UserGroupChangeRequest $request
     * @return array
     */
    public function updateUserGroup(UserGroupChangeRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupRepository->updateGroup($user_group->id, $request->fields);
    }

    /**
     * Delete User Group
     *
     * @param UserGroupDeleteRequest $request
     * @return mixed
     */
    public function deleteUserGroup(UserGroupDeleteRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->deleteGroup($user_group);
    }

    /**
     * @return User
     */
    public function reloadUserGroups()
    {
        $user = $this->userRepository->getWithNames(auth()->id());
        return $this->userGroupRepository->loadUserGroups($user);
    }

    /**
     * Add User to User Group
     *
     * @param UserGroups2UsersRequest $request
     * @return User
     */
    public function addUserToUserGroup(UserGroups2UsersRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        $this->userGroupService->addUserToGroup($user_group, $request->user_id);
        $user = $this->userRepository->getWithNames(auth()->id());
        return $this->userGroupRepository->loadUserGroups($user);
    }

    /**
     * Add User to User Group
     *
     * @param UserGroups2UsersRequest $request
     * @return array
     */
    public function updateUserInUserGroup(UserGroups2UsersRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return ['status' => $this->userGroupService->updateUserInGroup($user_group, (int)$request->user_id, !!$request->is_manager)];
    }

    /**
     * Delete User from User Group
     *
     * @param UserGroups2UsersRequest $request
     * @return mixed
     */
    public function deleteUserFromUserGroup(UserGroups2UsersRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->deleteUserFromGroup($user_group, $request->user_id);
    }

    /**
     * Add User Group Condition
     *
     * @param UserGroupAddConditionRequest $request
     * @return mixed
     */
    public function insertUserGroupCondition(UserGroupAddConditionRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);
        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->addGroupCondition(
            $user_group,
            array_merge($request->fields, ['user_group_id' => $request->user_group_id])
        );
    }

    /**
     * Update User Group Condition
     *
     * @param UserGroupUpdateConditionRequest $request
     * @return array
     */
    public function updateUserGroupCondition(UserGroupUpdateConditionRequest $request){
        $user_group = $this->userGroupRepository->getGroupByCondId($request->user_group_condition_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->updateGroupCondition(
            $user_group,
            $request->user_group_condition_id,
            array_merge($request->fields, ['user_group_id' => $user_group->id])
        );
    }

    /**
     * Delete User Group Condition
     *
     * @param UserGroupDeleteConditionRequest $request
     * @return mixed
     */
    public function deleteUserGroupCondition(UserGroupDeleteConditionRequest $request){
        $user_group = $this->userGroupRepository->getGroupByCondId($request->user_group_condition_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->deleteGroupCondition($user_group, $request->user_group_condition_id);
    }

    /**
     * Transfer Conditions to Individuals
     *
     * @param UserGroupDeleteRequest $request
     * @return mixed
     */
    public function changeCondToUsers(UserGroupDeleteRequest $request){
        $user_group = $this->userGroupRepository->getGroup($request->user_group_id);

        $this->authorize('isOwner', [UserGroup::class, $user_group]);

        return $this->userGroupService->changeCondToUsers($user_group);
    }
}
