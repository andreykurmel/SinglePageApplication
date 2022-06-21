<?php


namespace Vanguard\Services\Tablda;

use Illuminate\Support\Facades\Storage;
use Vanguard\Models\Folder\Folder;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Singletones\AuthUserSingleton;

class FolderService
{
    private $tableService;
    private $folderRepository;
    private $importService;

    /**
     * FolderService constructor.
     */
    public function __construct()
    {
        $this->tableService = new TableService();
        $this->folderRepository = new FolderRepository();
        $this->importService = new ImportService();
    }

    /**
     * Get folders tree for all tabs.
     *
     * @return array
     */
    public function getTree()
    {
        $auth = app(AuthUserSingleton::class);
        return $auth->getMenuTree();
    }

    /**
     * Get folder from user`s folders tree.
     *
     * @param $folder_id
     * @return mixed
     */
    public function getFolder($folder_id)
    {
        return $this->folderRepository->getFolder($folder_id);
    }

    /**
     * Get Folder by FolderView hash.
     *
     * @param $hash
     * @return array
     */
    public function getFolderByViewHash($hash)
    {
        return $this->folderRepository->getFolderByViewHash($hash);
    }

    /**
     * Get folder from user`s folders tree and load all needed settings.
     *
     * @param $folder_id
     * @param $user_id
     * @return mixed
     */
    public function getFolderMeta($folder_id, $user_id)
    {
        $folder = $this->folderRepository->getFolder($folder_id);
        $this->loadSettings($folder, $user_id);
        return $folder;
    }

    /**
     * @param $user_id
     * @param $object_id
     * @param $parent_id
     * @param string $type
     * @param string $structure
     * @return bool
     * @throws \Exception
     */
    public function createLink($user_id, $object_id, $parent_id, $type = 'link', $structure = 'private'): bool
    {
        $folder = $this->folderRepository->getFolder($object_id);

        if ($folder) {
            $folder_tree = $this->folderRepository->buildSubTree($folder);
            $this->linkSubfolders($folder_tree, $user_id, $parent_id, $type, $structure);

            //menutree is changed
            (new UserRepository())->newMenutreeHash($user_id);
            return true;
        }
        return false;
    }

    /**
     * @param $folder_tree
     * @param $user_id
     * @param int $parent_id
     * @param string $type
     * @param string $structure
     * @throws \Exception
     */
    protected function linkSubfolders($folder_tree, $user_id, $parent_id = 0, $type = 'link', $structure = 'private')
    {
        foreach ($folder_tree as $obj) {
            if ($obj['li_attr']['data-type'] == 'folder') {
                $arr = [ 'is_folder_link' => $user_id, 'is_opened' => 1 ];
                $new_folder = $this->folderRepository->insertFolder($parent_id, $user_id, $obj['init_name'], $structure, $arr);
                $this->linkSubfolders($obj['children'], $user_id, $new_folder->id, $type, $structure);
            } else {
                $arr = ['is_folder_link' => $user_id];
                $this->folderRepository->linkTable($obj['li_attr']['data-id'], $parent_id, $user_id, $type, $structure, $arr);
            }
        }
    }

    /**
     * Get folder from user`s folders tree with settings like TableService.
     *
     * @param Folder $folder
     * @param $user_id
     * @param string $structure
     */
    public function loadSettings(Folder $folder, $user_id, $structure = 'private')
    {
        $folder->_is_owner = $folder->user_id === $user_id;
        $folder->load([
            '_folder_views' => function ($q) {
                $q->with('_checked_tables');
            },
            '_root_folders'
        ]);

        $folder->_all_table_ids =
            $folder->_sub_folders()
                ->with([
                    '_tables' => function ($t) {
                        $t->where('folders_2_tables.type', 'table');
                    }
                ])
                ->get()
                ->pluck('_tables')
                ->flatten()
                ->pluck('id');

        $this->setAssignedViewNames($folder->_folder_views);

        //$this->getSubTree($folder);
        $auth = app(AuthUserSingleton::class);
        $folder->_sub_tree = $auth->getMenuTreeFolder($folder->id, $structure);
    }

    /**
     * Set Assigned View Names.
     *
     * @param $folder_views
     */
    public function setAssignedViewNames($folder_views)
    {
        $folder_views->load([
            '_pivot_tables' => function ($q) {
                $q->with('_assigned_view');
            }
        ]);
        foreach ($folder_views as $view) {
            $view->_assigned_view_names = $view->_pivot_tables
                ->flatten()
                ->pluck('_assigned_view')//get Views
                ->filter()//filter Null values
                ->values();
            unset($view->_pivot_tables);
        }
    }

    /**
     * Get Sub-Tree for selected Folder.
     *
     * @param Folder $folder
     * @param null $avail_tables
     */
    public function getSubTree(Folder $folder, $avail_tables = null)
    {
        $folder->_sub_tree = $this->folderRepository->buildSubTree($folder, $avail_tables);
        if (!$avail_tables) {
            $folder->_sub_tree = $folder->_sub_tree ? $folder->_sub_tree[0] : null;
        }
    }

    /**
     * Insert folder into user`s folders tree.
     *
     * @param $parent_id
     * @param $user_id
     * @param $name
     * @param $structure
     * @return array|Folder
     */
    public function insertFolder($parent_id, $user_id, $name, $structure, $in_shared)
    {
        if (
            $name
            &&
            $parent_id
            &&
            $this->folderRepository->testNameOnLvl($name, $parent_id, $user_id)
        ) {
            return ['error' => true, 'msg' => 'Node Name Taken. Enter a Different Name.'];
        }

        return $this->folderRepository->insertFolder($parent_id, $user_id, $name, $structure, ['in_shared' => $in_shared]);
    }

    /**
     * Update folder in the user`s folders tree.
     *
     * @param $folder_id
     * @param array $data - example:
     * [
     *  -name: folder,
     *  -is_opened: 0,
     * ]
     * @param $user_id
     * @return array
     */
    public function updateFolder($folder_id, array $data, $user_id)
    {
        return $this->folderRepository->updateFolder($folder_id, $data, $user_id);
    }

    /**
     * Transfer folder from the user`s folders tree to another user.
     *
     * @param Folder $folder
     * @param int $new_user_id
     * @return mixed
     */
    public function transferFolder(Folder $folder, $new_user_id)
    {
        $sys_folder = $this->folderRepository->getSysFolder($new_user_id, 'TRANSFERRED');
        $this->moveFolder($folder, $sys_folder->id, $new_user_id);

        return $this->transferFolderAndSubFolders($folder, $new_user_id);
    }

    /**
     * Transfer folder from the user`s folders tree to another user.
     *
     * @param Folder $folder
     * @param int $new_parent_id = null
     * @param int $user_id
     * @return mixed
     */
    public function moveFolder(Folder $folder, $new_parent_id, $user_id, int $position = null)
    {
        $folderWithSub = $this->folderRepository->getFolderWithSubFolders($folder->id);
        $table_ids = $this->tableService->getIdsFromFolders($folderWithSub->pluck('id'));
        $this->tableService->syncStructureOfShared($table_ids, $user_id);
        $this->folderRepository->updatePosition($folder, $user_id, $new_parent_id, $position);

        return $this->folderRepository->moveFolder($folder->id, $new_parent_id, $user_id);
    }

    /**
     * Transfer Folder with subfolders and tables in provided folders.
     *
     * @param Folder $folder
     * @param $new_user_id
     * @return int
     */
    public function transferFolderAndSubFolders(Folder $folder, $new_user_id)
    {
        $sub_folders = $this->folderRepository->getFolderWithSubFolders($folder->id);
        $sub_folders->load('_tables');
        foreach ($sub_folders as $sub_folder) {
            foreach ($sub_folder->_tables as $sub_table) {
                $this->tableService->transferTable($sub_table, $new_user_id, false);
            }
            $this->folderRepository->transferFolder($sub_folder, $new_user_id);
        }

        return 1;
    }

    /**
     * Get 'TRANSFERRED' folder for User
     *
     * @param $user_id
     * @return mixed
     */
    public function getSysFolder($user_id)
    {
        return $this->folderRepository->getSysFolder($user_id, 'TRANSFERRED');
    }

    /**
     * Recreate Checked Tables for FolderView.
     *
     * @param $folder_view_id
     * @param array $checked_tables : ids of checked tables [1, 3, 45, 12, ...]
     * @return mixed
     */
    public function updateFolderViews($folder_view_id, array $checked_tables)
    {
        return $this->folderRepository->updateFolderViews($folder_view_id, $checked_tables);
    }

    /**
     * Add Icon to Folder.
     *
     * @param Folder $folder
     * @param $file
     * @return string
     */
    public function addIcon(Folder $folder, $file)
    {
        $fileName = 'folder_icons/' . $folder->id . '_';
        $fileName .= preg_replace('/[\s\?&]/i', '_', $file->getClientOriginalName());
        $file->storeAs("public/", $fileName);

        $folder->icon_path = $fileName;
        $folder->save();

        return $fileName;
    }

    /**
     * Delete Icon from Folder.
     *
     * @param Folder $folder
     * @return bool
     */
    public function delIcon(Folder $folder)
    {
        Storage::delete('public/' . $folder->icon_path);
        $folder->icon_path = '';
        return $folder->save();
    }

    /**
     * Toggle table in favorite for user.
     *
     * @param int $folder_id
     * @param int $user_id
     * @param bool $favorite
     * @return int
     */
    public function favoriteToggle($folder_id, $user_id, $favorite)
    {
        $folder = $this->folderRepository->getFolder($folder_id);

        //delete already present folders and subfolders in favorite tab.
        $folderFavorite = $this->folderRepository->getFavoriteFolder($folder->name, $user_id);
        if ($folderFavorite) {
            $this->deleteFolderWithSubs($folderFavorite->id, $user_id);
        }

        //add to favorite if selected
        if ($favorite) {
            $folder_tree = $this->folderRepository->buildSubTree($folder);
            $this->copyFolderToFavorite($folder_tree, $user_id);
        }

        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        return 1;
    }

    /**
     * Delete folders from the user`s folders tree.
     * And subfolders + tables in those folders.
     *
     * @param int $folder_id
     * @param int $user_id
     * @return mixed
     */
    public function deleteFolderWithSubs(int $folder_id, int $user_id)
    {
        $folders = $this->folderRepository->getFolderWithSubFolders($folder_id);
        $folders->load('_tables');
        //delete tables in folder and subfolders.
        foreach ($folders as $folder) {
            foreach ($folder->_tables as $sub_table) {
                if ($sub_table->link->type == 'table') {
                    $this->importService->deleteTable($sub_table);
                } else {
                    $folder->_tables()->detach($sub_table->id);
                }
            }
        }

        //delete folder and subfolders
        $folder_ids = $folders->pluck('id')->toArray();
        return $this->folderRepository->deleteFolders($folder_ids, $user_id);
    }

    /**
     * Copy selected Folder with Subfolders to user's favorite tab.
     *
     * @param array $folder_tree
     * @param $user_id
     * @param int $parent_id
     */
    public function copyFolderToFavorite($folder_tree, $user_id, $parent_id = 0)
    {
        foreach ($folder_tree as $obj) {
            if ($obj['li_attr']['data-type'] == 'folder') {
                $new_folder = $this->folderRepository->insertFolder($parent_id, $user_id, $obj['init_name'], 'favorite');
                $this->copyFolderToFavorite($obj['children'], $user_id, $new_folder->id);
            } else {
                $this->folderRepository->linkTable($obj['li_attr']['data-id'], $parent_id, $user_id, 'link', 'favorite');
            }
        }
    }

    /**
     * Get Folder View.
     *
     * @param $hash
     * @return mixed
     */
    public function folderViewExists($hash)
    {
        $path = explode('/', $hash);
        if (count($path) > 1) {
            $folder_view = $this->folderRepository->getByFldAndAddress($path);
        } else {
            $folder_view = $this->folderRepository->getByHash($hash);
        }
        ($folder_view ? $folder_view->load('_folder', '_checked_tables') : '');

        if ($folder_view) {
            $folder_view->_for_user_or_active = $folder_view->is_active;
        }
        return $folder_view;
    }
}