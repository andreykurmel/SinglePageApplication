<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderViewAddRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderViewChangeRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\FolderViewDeleteRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\GetFolderViewsTableRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\SetFolderViewsRequest;
use Vanguard\Http\Requests\Tablda\FolderPermission\SetFolderViewsTableRequest;
use Vanguard\Models\Folder\Folder;
use Vanguard\Repositories\Tablda\Permissions\FolderViewRepository;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class FolderViewController extends Controller
{
    private $folderService;
    private $folderViewRepository;
    private $tableService;

    /**
     * FolderViewController constructor.
     *
     * @param FolderService $folderService
     * @param FolderViewRepository $folderViewRepository
     * @param TableService $tableService
     */
    public function __construct(
        FolderService $folderService,
        FolderViewRepository $folderViewRepository,
        TableService $tableService
    )
    {
        $this->folderService = $folderService;
        $this->folderViewRepository = $folderViewRepository;
        $this->tableService = $tableService;
    }

    /**
     * Add Folder View
     *
     * @param FolderViewAddRequest $request
     * @return mixed
     */
    public function insertFolderView(FolderViewAddRequest $request){
        $folder = $this->folderService->getFolder($request->folder_id) ?? new Folder();

        $this->authorize('isOwner', [Folder::class, $folder]);

        return $this->folderViewRepository->addView([
            'name' => $request->name,
            'user_link' => $request->user_link,
            'folder_id' => $request->folder_id
        ]);
    }

    /**
     * Update Folder View
     *
     * @param FolderViewChangeRequest $request
     * @return array
     */
    public function updateFolderView(FolderViewChangeRequest $request){
        $view = $this->folderViewRepository->getView($request->folder_view_id);
        $folder = $this->folderService->getFolder($view->folder_id) ?? new Folder();

        $this->authorize('isOwner', [Folder::class, $folder]);

        return $this->folderViewRepository->updateView($view->id, $request->fields);
    }

    /**
     * Delete Folder View
     *
     * @param FolderViewDeleteRequest $request
     * @return mixed
     */
    public function deleteFolderView(FolderViewDeleteRequest $request){
        $view = $this->folderViewRepository->getView($request->folder_view_id);
        $folder = $this->folderService->getFolder($view->folder_id) ?? new Folder();

        $this->authorize('isOwner', [Folder::class, $folder]);

        return $this->folderViewRepository->deleteView($view->id);
    }

    /**
     * Set Views UserGroups to Folder and sub-folders
     *
     * @param SetFolderViewsRequest $request
     * @return array|bool|null
     */
    public function setFolderViews(SetFolderViewsRequest $request) {
        $view = $this->folderViewRepository->getView($request->folder_view_id);
        $folder = $this->folderService->getFolder($view->folder_id) ?? new Folder();

        $this->authorize('isOwner', [Folder::class, $folder]);

        return $this->folderService->updateFolderViews($request->folder_view_id, $request->checked_tables);
    }

    /**
     * Get Folder View Table
     *
     * @param GetFolderViewsTableRequest $request
     * @return array|bool|null
     */
    public function getFolderViewTable(GetFolderViewsTableRequest $request) {
        $view = $this->folderViewRepository->getView($request->folder_view_id);
        $folder = $this->folderService->getFolder($view->folder_id) ?? new Folder();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [Folder::class, $folder]);

        return [
            'table_views' => $table->_views()->get(),
            'folder_view_table' => $this->folderViewRepository->getFolderViewTable($request->folder_view_id, $request->table_id)
        ];
    }

    /**
     * Get Folder View Table
     *
     * @param SetFolderViewsTableRequest $request
     * @return array|bool|null
     */
    public function setFolderViewTable(SetFolderViewsTableRequest $request) {
        $view = $this->folderViewRepository->getView($request->folder_view_id);
        $folder = $this->folderService->getFolder($view->folder_id) ?? new Folder();

        $this->authorize('isOwner', [Folder::class, $folder]);

        return ['status' => $this->folderViewRepository->setFolderViewTable($request->folder_view_id, $request->table_id, $request->assigned_view_id)];
    }
}
