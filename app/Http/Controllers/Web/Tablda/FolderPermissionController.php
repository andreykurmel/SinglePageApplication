<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionAddRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionChangeRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderPermissionDeleteRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\SetFolderPermissionsRequest;
use Vanguard\Models\Folder\Folder;
use Vanguard\Repositories\Tablda\Permissions\FolderPermissionRepository;
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
     * Set Permissions UserGroups to Folder and sub-folders
     *
     * @param SetFolderPermissionsRequest $request
     * @return array|bool|null
     */
    public function setFolderPermissions(SetFolderPermissionsRequest $request) {
        $user_group = $this->folderPermissionRepository->getUserGroup($request->user_group_id);
        if (auth()->id() == $user_group->user_id)
        {
            $this->folderPermissionRepository->updatePermissions($user_group, $request->is_active, $request->is_app, $request->old_tables, $request->checked_tables);
            return $user_group->_tables_shared()->get();
        } else {
            return response('Forbidden', 403);
        }
    }
}
