<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionAddRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionChangeRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionDeleteRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\SetFolderPermissionsRequest;
use Vanguard\Models\Folder\Folder;
use Vanguard\Repositories\Tablda\Permissions\FolderPermissionRepository;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\User;

class FolderPermissionController extends Controller
{
    private $folderPermissionRepository;

    /**
     * FolderPermissionController constructor.
     */
    public function __construct()
    {
        $this->folderPermissionRepository = new FolderPermissionRepository();
    }

    /**
     * @param SetFolderPermissionsRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function setFolderPermissions(SetFolderPermissionsRequest $request) {
        $user_group = $this->folderPermissionRepository->getUserGroup($request->user_group_id);
        if (auth()->id() == $user_group->user_id)
        {
            $user_ids = (new UserGroupRepository())->getGroupUsrFields($user_group->id, 'id');
            (new UserRepository())->newMenutreeHash($user_ids);

            $user_group->is_system
                ? $this->folderPermissionRepository->updateSystemPermissions($user_group, $request->is_active, $request->is_app, $request->checked_tables)
                : $this->folderPermissionRepository->updatePermissions($user_group, $request->is_active, $request->is_app, $request->old_tables, $request->checked_tables);

            return response('Success', 200);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function assignOnePermission(Request $request) {
        $user_group = $this->folderPermissionRepository->getUserGroup($request->user_group_id);
        if (auth()->id() == $user_group->user_id)
        {
            $this->folderPermissionRepository->assignPermission($user_group, $request->tb_shared_id, $request->permission_id);
            return response('Success', 200);
        } else {
            return response('Forbidden', 403);
        }
    }
}
