<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Vanguard\Classes\Tablda;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\Folder\AddIconFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\CopyFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\DeleteFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\FavoriteToggleRequest;
use Vanguard\Http\Requests\Tablda\Folder\GetFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\PostFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\PutFolderRequest;
use Vanguard\Http\Requests\Tablda\Folder\TransferFolderRequest;
use Vanguard\Http\Requests\Tablda\GetLinkRequest;
use Vanguard\Http\Requests\Tablda\MoveNodeRequest;
use Vanguard\Http\Requests\Tablda\PostLinkRequest;
use Vanguard\Models\Folder\Folder;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\ImportService;
use Vanguard\User;

class FolderController extends Controller
{
    private $service;
    private $folderService;
    private $importService;

    /**
     * FolderController constructor.
     *
     * @param FolderService $folderService
     * @param HelperService $service
     * @param ImportService $importService
     */
    public function __construct(
        FolderService $folderService,
        HelperService $service,
        ImportService $importService
    )
    {
        $this->service = $service;
        $this->folderService = $folderService;
        $this->importService = $importService;
    }

    /**
     * Get Menu-tree for auth user.
     *
     * @return array
     */
    public function getMenuTree()
    {
        return $this->folderService->getTree();
    }

    /**
     * Get Settings for selected Folder with all UserGroups.
     *
     * @param GetFolderRequest $request
     * @return mixed
     */
    public function getSettings(GetFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->folder_id) ?? new Folder();
        if ($folder->user_id) { //if not 'public'
            $this->authorize('isOwner', [Folder::class, $folder]);
        }
        $this->folderService->loadSettings($folder, auth()->id(), $request->structure ?: 'private');
        return [
            'folder' => $folder,
        ];
    }

    /**
     * Insert folder into user`s folders tree.
     *
     * @param PostFolderRequest $request
     * @return array
     */
    public function insert(PostFolderRequest $request)
    {
        $structures = auth()->user()->isAdmin() ? ['public', 'private', 'favorite'] : ['private', 'favorite'];

        $request->parent_id = $request->parent_id ?: 0;
        $request->name = Tablda::safeName($request->name);
        $folder = $this->folderService->insertFolder(
            $request->parent_id,
            auth()->id(),
            $request->name,
            in_array($request->structure, $structures) ? $request->structure : 'private',
            $request->description,
            $request->in_shared
        );
        if (empty($folder['error'])) {
            return $this->service->getFolderObjectForMenuTree($folder, [], $request->path);
        } else {
            return response($folder, 500);
        }
    }

    /**
     * Update folder in the user`s folders tree.
     *
     * @param PutFolderRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PutFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->folder_id);
        if ($folder) {
            $this->authorize('isOwner', [Folder::class, $folder]);
        } else {
            return [];
        }

        $resp = $this->folderService->updateFolder($request->folder_id, $request->fields, auth()->id());
        return response($resp, (empty($resp['error']) ? 200 : 500));
    }

    /**
     * Delete folders from the user`s folders tree.
     * And subfolders + tables in those folders.
     *
     * @param DeleteFolderRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DeleteFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->folder_id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return ['status' => $this->folderService->deleteFolderWithSubs($request->folder_id, $folder->user_id ?: auth()->id())];
    }

    /**
     * Transfer folders from the user`s folders tree to another user.
     * And subfolders + tables in those folders.
     *
     * @param TransferFolderRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function transfer(TransferFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return ['status' => $this->folderService->transferFolder($folder, $request->new_user_id)];
    }

    /**
     * Move folder to another folder.
     *
     * @param MoveNodeRequest $request
     * @return array
     */
    public function move(MoveNodeRequest $request)
    {
        $folder = $this->folderService->getFolder($request->id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return ['status' => $this->folderService->moveFolder($folder, $request->folder_id, auth()->id(), $request->position)];
    }

    /**
     * Copy Folder with Sub-Folders adn Tables from the user`s folders tree to another user.
     *
     * @param CopyFolderRequest $request
     * @return array|bool|null
     */
    public function copyTo(CopyFolderRequest $request)
    {
        $sys_folder = $this->folderService->getSysFolder($request->new_user_id);
        return $this->importService->copyFolderAndSubfolder(
            $request->folder_json,
            $request->new_user_id,
            $request->direct_folder_id ?: $sys_folder->id,
            $request->new_name ?: '',
            !!$request->overwrite,
            !!$request->with_links
        );
    }

    /**
     * @param PostLinkRequest $request
     * @return array
     * @throws \Exception
     */
    public function createLink(PostLinkRequest $request)
    {
        $folder = $this->folderService->getFolder($request->object_id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return [
            'result' => $this->folderService->createLink(auth()->id(), $request->object_id, $request->folder_id, $request->type, $request->structure)
        ];
    }

    /**
     * Add Icon to Folder.
     *
     * @param AddIconFolderRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addIcon(AddIconFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->folder_id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return $this->folderService->addIcon($folder, $request->file('file'));
    }

    /**
     * Delete Icon from Folder.
     *
     * @param GetFolderRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delIcon(GetFolderRequest $request)
    {
        $folder = $this->folderService->getFolder($request->folder_id) ?? new Folder();
        $this->authorize('isOwner', [Folder::class, $folder]);

        return ['status' => $this->folderService->delIcon($folder)];
    }

    /**
     * Toggle folder with subfolders in favorite for user.
     *
     * @param FavoriteToggleRequest $request
     * @return array|bool|null
     */
    public function favorite(FavoriteToggleRequest $request)
    {
        return $this->folderService->favoriteToggle($request->folder_id, auth()->id(), $request->favorite);
    }
}
