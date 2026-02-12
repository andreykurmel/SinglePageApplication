<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Folder\FolderView;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\FolderViewTable;
use Vanguard\Models\User\UserGroup;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class FolderViewRepository
{
    protected $service;

    /**
     * FolderViewRepository constructor.
     *
     * @param HelperService $service
     */
    public function __construct(HelperService $service)
    {
        $this->service = $service;
    }

    /**
     * Get View.
     *
     * @param $folder_view_id
     * @return mixed
     */
    public function getView($folder_view_id)
    {
        return FolderView::where('id', '=', $folder_view_id)->first();
    }

    /**
     * Get Folder View Table.
     *
     * @param $folder_view_id
     * @param $table_id
     * @return mixed
     */
    public function getFolderViewTable($folder_view_id, $table_id)
    {
        return FolderViewTable::where('folder_view_id', $folder_view_id)
            ->where('table_id', $table_id)
            ->first();
    }

    /**
     * Set Folder View Table.
     *
     * @param $folder_view_id
     * @param $table_id
     * @param $assigned_view_id
     * @return mixed
     */
    public function setFolderViewTable($folder_view_id, $table_id, $assigned_view_id)
    {
        return FolderViewTable::where('folder_view_id', $folder_view_id)
            ->where('table_id', $table_id)
            ->update([
                'assigned_view_id' => $assigned_view_id
            ]);
    }

    /**
     * Add View.
     *
     * @param $data
     * [
     *  +name: string,
     *  +folder_id: int,
     * ]
     * @return mixed
     */
    public function addView($data)
    {
        $data['hash'] = Uuid::uuid4();
        $data['side_top'] = $data['side_top'] ?? 'na';
        $data['side_left_menu'] = $data['side_left_menu'] ?? 'show';
        $data['side_left_filter'] = $data['side_left_filter'] ?? 'show';
        $data['side_right'] = $data['side_right'] ?? 'na';
        $folder_view = FolderView::create( $this->service->delSystemFields($data) );
        $folder_view->_checked_tables = [];
        return $folder_view;
    }

    /**
     * Update View
     *
     * @param int $view_id
     * @param $data
     * [
     *  -folder_id: int,
     *  -name: string,
     *  -notes: string
     * ]
     * @return array
     */
    public function updateView($view_id, $data)
    {
        return FolderView::where('id', $view_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete View
     *
     * @param int $view_id
     * @return mixed
     */
    public function deleteView($view_id)
    {
        return FolderView::where('id', $view_id)->delete();
    }
}