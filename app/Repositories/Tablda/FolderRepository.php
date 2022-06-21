<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\Models\Folder\FolderStructure;
use Vanguard\Models\Folder\FolderView;
use Vanguard\Models\Folder\FolderViewTable;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableView;
use Vanguard\Services\Tablda\HelperService;

class FolderRepository
{
    protected $service;

    /**
     * FolderRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param array $arr
     * @return mixed
     */
    public function whereFind(array $arr)
    {
        return Folder::where($arr)->first();
    }

    /**
     * Get Near Sub Folders for selected Folder.
     *
     * @param int $folder_id
     * @return Collection
     */
    public function getNearSubFolders(int $folder_id)
    {
        return Folder::join('folder_trees', function ($q) {
            $q->whereRaw('folder_trees.child_id = folders.id');
            $q->whereRaw('folder_trees.element_id = folders.id');
        })
            ->where('folder_trees.parent_id', $folder_id)
            ->select('folders.*', 'folder_trees.parent_id')
            ->get();
    }

    /**
     * Get Favorite Folder.
     *
     * @param string $folder_name
     * @param int $user_id
     * @return mixed
     */
    public function getFavoriteFolder(string $folder_name, int $user_id)
    {
        return Folder::where('name', $folder_name)
            ->where('user_id', $user_id)
            ->where('structure', 'favorite')
            ->first();
    }

    /**
     * Build Sub Tree for provided Folder.
     *
     * @param Folder $folder
     * @param $avail_tables
     * @return array
     */
    public function buildSubTree(Folder $folder, $avail_tables = null)
    {
        $sub_folders = $this->getFolderWithSubFolders($folder->id);
        $sub_folders->load([
            '_tables' => function ($t) use ($avail_tables) {
                $t->where('folders_2_tables.type', 'table');
                if ($avail_tables) {
                    $t->whereIn('tables.id', $avail_tables);
                }
            }
        ]);
        $parent_folder = $sub_folders->where('id', $folder->id)->first();

        return $this->simpleSubTree($sub_folders, $parent_folder->parent_id, $avail_tables);
    }

    /**
     * Get With Sub Folders.
     *
     * @param int $folder_id
     * @return Collection
     */
    public function getFolderWithSubFolders(int $folder_id)
    {
        return Folder::join('folder_trees', 'folder_trees.child_id', '=', 'folders.id')
            ->where('folder_trees.element_id', $folder_id)
            ->select('folders.*', 'folder_trees.parent_id')
            ->get();
    }

    /**
     * Build Sub Tree for provided Folder.
     *
     * @param $folders
     * @param int $parentId
     * @param array $avail_tables
     * @param string $ahref
     * @return array
     */
    private function simpleSubTree($folders, $parentId = 0, $avail_tables = null, $ahref = '/')
    {
        $branch = [];

        foreach ($folders as $folder) {
            if ($folder->parent_id == $parentId) {
                $folder_link = $ahref . $folder->name . '/';
                $children = [
                    'sub_tables' => [],
                    'tree' => []
                ];

                foreach ($folder->_tables as $table) {
                    $tb_arr = $this->service->getTableObjectForMenuTree($table, 'table', $folder_link);
                    $tb_arr['state']['selected'] = !!$avail_tables;
                    $children['sub_tables'][] = $tb_arr;
                }

                $children['tree'] = $this->simpleSubTree($folders, $folder->id, $avail_tables, $folder_link);

                if ($avail_tables && !$children['sub_tables'] && !$children['tree']) {
                    continue;
                }

                $branch[] = $this->service->getFolderObjectForMenuTree($folder, $children, $folder_link, true);
            }
        }

        return $branch;
    }

    /**
     * Link table to folder.
     *
     * @param $table_id
     * @param $folder_id
     * @param $user_id
     * @param string $type
     * @param string $structure
     * @param array $additionals
     * @return Folder2Table
     * @throws Exception
     */
    public function linkTable($table_id, $folder_id, $user_id, $type = 'table', $structure = 'private', $additionals = [])
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        $link = Folder2Table::create(array_merge(
            [
                'table_id' => $table_id,
                'folder_id' => $folder_id,
                'user_id' => $user_id,
                'type' => $type,
                'structure' => $structure,
                'is_folder_link' => $additionals['is_folder_link'] ?? 0,
            ],
            $this->service->getCreated(),
            $this->service->getModified()
        ));
        if ($structure == 'public') {
            (new TableRepository())->updateIsPublic($table_id, 1);
        }
        return Folder2Table::join('tables', 'tables.id', '=', 'table_id')
            ->where('folders_2_tables.id', '=', $link->id)
            ->select('*', 'tables.user_id as table_user_id', 'folders_2_tables.user_id as link_user_id', 'folders_2_tables.id as link_id')
            ->first();
    }

    /**
     * unlink table from folder.
     *
     * @param $table_id
     * @param $user_id
     * @param $folder_id
     * @param string $type
     * @param string $structure
     * @return mixed
     */
    public function unlinkTable($table_id, $folder_id, $user_id, $type = 'table', $structure = 'private')
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        if ($structure == 'public') {
            (new TableRepository())->updateIsPublic($table_id, 0);
        }
        return Folder2Table::where('table_id', '=', $table_id)
            ->where('folder_id', '=', $folder_id)
            ->where('user_id', '=', $user_id)
            ->where('type', '=', $type)
            ->where('structure', '=', $structure)
            ->delete();
    }

    /**
     * Add Sys Folders for nonUsers.
     *
     * @param $folder_name
     * @param null $user_id
     */
    public function addSysFolderWithSysTables($folder_name, $user_id = null)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        if ($folder_name == 'System') {
            $sys_tables = Table::where('is_system', '=', 1)
                ->whereNotIn('db_name', $this->service->system_tables_for_all)
                ->get();
        } elseif ($folder_name == 'Support') {
            $supports = array_merge($this->service->support_tables, $this->service->admin_support);
            $sys_tables = Table::whereIn('db_name', $supports)->get();
        } elseif ($folder_name == 'myAccount') {
            $sys_tables = Table::whereIn('db_name', $this->service->myaccount_tables)->get();
        } elseif ($folder_name == 'Info') {
            $sys_tables = Table::whereIn('db_name', $this->service->info_tables)->get();
        } else {
            $sys_tables = Table::whereIn('db_name', $this->service->correspondence_tables)->get();
            $fld_support = $this->getSysFolder(null, 'Support', 'account');
            $this->insertFolder($fld_support->id, null, 'Correspondence', 'account', ['is_system' => 1]);
        }

        $fld = $this->getSysFolder($user_id, $folder_name, 'account');
        Folder2Table::where('folder_id', $fld->id)->delete();
        foreach ($sys_tables as $tb) {
            Folder2Table::insert([
                'table_id' => $tb->id,
                'user_id' => 1,
                'folder_id' => $fld->id,
                'type' => 'link',
                'structure' => 'account',
            ]);
        }
    }

    /**
     * Get ID of system folder.
     * Create if doesn`t exists.
     *
     * @param $user_id
     * @param string $folder_name
     * @param string $structure
     * @return mixed
     */
    public function getSysFolder($user_id, $folder_name = 'SHARED', $structure = 'private')
    {
        $folder = Folder::where('user_id', '=', $user_id)
            ->where('is_system', '=', 1)
            ->where('name', '=', $folder_name)
            ->first();

        if (!$folder) {
            $folder = Folder::create(array_merge(
                [
                    'user_id' => $user_id,
                    'is_system' => 1,
                    'name' => $folder_name,
                    'structure' => $structure,
                ],
                $this->service->getCreated(),
                $this->service->getModified()
            ));
            FolderStructure::create([
                'element_id' => $folder->id,
                'child_id' => $folder->id
            ]);
        }

        return $folder;
    }

    /**
     * Insert folder into user`s folders tree.
     *
     * @param $parent_id
     * @param $user_id
     * @param $name
     * @param $structure
     * @param array $additions
     * @return Folder
     * @throws Exception
     */
    public function insertFolder($parent_id, $user_id, $name, $structure, $additions = [])
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        $inserts = array_merge(
            $this->service->delSystemFields([
                'user_id' => $user_id,
                'name' => trim($name),
                'structure' => $structure,
                'is_opened' => $additions['is_opened'] ?? 0,
                'is_system' => $additions['is_system'] ?? 0,
                'in_shared' => $additions['in_shared'] ?? 0,
                'is_folder_link' => $additions['is_folder_link'] ?? 0,
                'for_shared_user_id' => $additions['for_shared_user_id'] ?? null,
            ]),
            $this->service->getModified(),
            $this->service->getCreated()
        );

        //show 'public' to all users.
        if ($structure == 'public') {
            $inserts['user_id'] = null;
        }

        $folder = Folder::create($inserts);

        $parent = FolderStructure::where('element_id', $parent_id)
            ->where('child_id', $parent_id)
            ->first();
        $add_links = FolderStructure::where('child_id', $parent_id)->get();

        $structure_arr = [
            [
                'element_id' => $folder->id,
                'child_id' => $folder->id,
                'parent_id' => $parent ? $parent->element_id : 0,
            ]
        ];
        foreach ($add_links as $link) {
            $structure_arr[] = [
                'element_id' => $link->element_id,
                'child_id' => $folder->id,
                'parent_id' => $parent ? $parent->element_id : 0,
            ];
        }
        FolderStructure::insert($structure_arr);

        return $folder;
    }

    /**
     * Get folder from user`s folders tree.
     *
     * @param $folder_id
     * @return mixed
     */
    public function getFolder($folder_id)
    {
        return Folder::where('id', '=', $folder_id)->first();
    }

    /**
     * Get Folder by FolderView hash.
     *
     * @param $hash
     * @return array
     */
    public function getFolderByViewHash($hash)
    {
        return [
            'folder' => Folder::whereHas('_folder_views', function ($q) use ($hash) {
                $q->where('hash', $hash);
            })
                ->first(),

            'avail_tables' => FolderViewTable::whereHas('_folder_view', function ($q) use ($hash) {
                $q->where('hash', $hash);
            })
                ->get()
                ->pluck('table_id')
        ];
    }

    /**
     * Insert all needed system Folders for 'private' user's tab.
     *
     * @param $user_id
     */
    public function insertSystems($user_id)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        Folder::where('structure', 'private')
            ->where('user_id', $user_id)
            ->where('is_system', 1)
            ->delete();

        $this->insertFolder(0, $user_id, 'SHARED', 'private', ['is_system' => 1, 'in_shared' => 1]);
        $this->insertFolder(0, $user_id, 'TRANSFERRED', 'private', ['is_system' => 1]);
        $this->insertFolder(0, $user_id, 'APPs', 'private', ['is_system' => 1, 'in_shared' => 2]);
    }

    /**
     * Copy shared folders structure to target User.
     *
     * @param $folder_id
     * @param $new_parent
     * @param $user_id
     * @return mixed
     */
    /*public function copyFolderStructure($folder_id, $new_parent, $user_id) {
        //save present parent
        $par = FolderStructure::join('folders', 'folders.id', '=', 'folder_trees.parent_id')
            ->where('folder_trees.element_id', $folder_id)
            ->where('folder_trees.child_id', $folder_id)
            ->where('folder_trees.user_id', $user_id)
            ->with('_parent')
            ->first();

        $new_parent = ($par && $par->_parent && !$par->_parent->is_system) ? $par->parent_id : $new_parent;

        //del old structure
        FolderStructure::where('element_id', $folder_id)
            ->where('user_id', $user_id)
            ->delete();

        //copy new structure
        $structure = FolderStructure::where('element_id', $folder_id)
            ->select(['element_id','child_id','parent_id'])
            ->get();

        foreach ($structure as $elem) {
            $elem->user_id = $user_id;
            if ($elem->element_id == $elem->child_id) {
                $elem->parent_id = $new_parent;
            }
        }

        return FolderStructure::insert($structure->toArray());
    }*/

    /**
     * Move folder with subfolders.
     *
     * @param $folder_id
     * @param int $parent_id
     * @param int $user_id
     * @return bool
     * @throws Exception
     */
    public function moveFolder($folder_id, $parent_id = 0, $user_id = 0)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        //get moved subfolders
        $target_children = FolderStructure::where('element_id', $folder_id)->get();
        $target_children_ids = $target_children->pluck('child_id');
        $target = $target_children->where('child_id', $folder_id)->first();

        //delete from old_parent structure
        FolderStructure::whereNotIn('element_id', $target_children_ids)
            ->whereIn('child_id', $target_children_ids)
            ->delete();

        //update target
        $target->parent_id = $parent_id;
        $target->save();

        //insert into new_parent structure
        $add_links = FolderStructure::where('child_id', $parent_id)->get();
        $structure_arr = [];
        foreach ($add_links as $link) {
            foreach ($target_children as $tch) {
                $structure_arr[] = [
                    'element_id' => $link->element_id,
                    'child_id' => $tch->child_id,
                    'parent_id' => $tch->parent_id,
                ];
            }
        }
        return FolderStructure::insert($structure_arr);
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
     * @return array
     */
    public function updateFolder($folder_id, array $data, $user_id)
    {
        //menutree is changed
        if (!empty($data['name']) || !empty($data['is_opened'])) {
            $hash = (new UserRepository())->newMenutreeHash($user_id);
        } else {
            $hash = (new UserRepository())->getMenutreeHash($user_id);
        }

        $upd = collect($data)->only([
                'name', 'is_opened', 'icon_path', 'import_source', 'importfolder_airtable_save',
                'importfolder_google_save', 'importfolder_dropbox_save', 'importfolder_onedrive_save',
                'importfolder_ocr_save', 'importfolder_local_save',
            ])
            ->toArray();

        Folder::where('id', '=', $folder_id)
            ->update($upd);

        return $hash;
    }

    /**
     * Delete folders from the user`s folders tree.
     *
     * @param array $folder_ids
     * @return mixed
     */
    public function deleteFolders(array $folder_ids, $user_id)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        return Folder::whereIn('id', $folder_ids)
            ->delete();
    }

    /**
     * Transfer folders from the user`s folders tree to another user.
     *
     * @param Folder $folder
     * @param int $new_user_id
     * @return mixed
     */
    public function transferFolder(Folder $folder, $new_user_id)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($new_user_id);

        //delete links
        $folder->_table_links()->where('type', '!=', 'table')->delete();

        return $folder->update(array_merge(['user_id' => $new_user_id], $this->service->getModified()));
    }

    /**
     * Test that folder with provided name already exists.
     *
     * @param $folder_name
     * @param $parent_id
     * @param $user_id
     * @return mixed
     */
    public function testNameOnLvl($folder_name, $parent_id, $user_id)
    {
        return Folder::join('folder_trees', 'folder_trees.element_id', '=', 'folders.id')
            ->select('folders.*', 'folder_trees.parent_id')
            ->where('name', '=', $folder_name)
            ->where('folders.user_id', '=', $user_id)
            ->where('parent_id', '=', $parent_id)
            ->whereNull('folder_trees.user_id')
            ->first();
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
        FolderViewTable::where('folder_view_id', $folder_view_id)->delete();
        $arr = [];
        foreach ($checked_tables as $checked_table) {
            $arr[] = [
                'folder_view_id' => $folder_view_id,
                'table_id' => $checked_table['id'],
            ];
        }
        FolderViewTable::insert($arr);
        return Table::whereIn('id', collect($checked_tables)->pluck('id'))->get();
    }

    /**
     * Update Links positions.
     *
     * @param Folder $folder
     * @param $user_id
     * @param $folder_id
     * @param $position
     */
    public function updatePosition(Folder $folder, $user_id, $folder_id, $position)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        $position = max($position, 0);
        $folder->menutree_order = $position;
        $folder->save();

        $links = Folder::joinFolderStructure()
            ->where('folders.user_id', '=', $user_id)
            ->where('parent_id', '=', $folder_id)
            ->where('folders.id', '!=', $folder->id)
            ->orderBy('menutree_order')
            ->get();
        $idx = -1;
        foreach ($links as $link) {
            $idx++;
            if ($idx == $position) {
                $idx++;
            }
            $link->menutree_order = $idx;
            $link->save();
        }
    }

    /**
     * Get Folder View.
     *
     * @param $hash
     * @return mixed
     */
    public function getByHash($hash)
    {
        return FolderView::where('hash', $hash)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Get Folder View.
     *
     * @param $param
     * @return mixed
     */
    public function getByFldAndAddress($param)
    {
        /*return FolderView::whereHas('_folder', function ($t) use ($param) {
                $t->where('id', '=', $param[0]);
            })*/
        return FolderView::where('hash', '=', $param[0])
            ->where('user_link', $param[2])
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Get table View Hash for selected FolderView and Table.
     *
     * @param $hash
     * @param $table_id
     * @return int
     */
    public function getAssignedView($hash, $table_id)
    {
        $folder_view_table = FolderViewTable::whereHas('_folder_view', function ($q) use ($hash) {
            $q->where('hash', $hash);
        })
            ->where('table_id', $table_id)
            ->first();

        $table_view = ($folder_view_table ? TableView::where('id', $folder_view_table->assigned_view_id)->first() : null);
        if (!$table_view) {
            $table_view = (new TableViewRepository())->getSys($table_id);
        }

        return $table_view->hash;
    }

    /**
     * Build path from Root Folders.
     *
     * @param $folders
     * @param int $parent_id
     * @return string
     */
    public function buildPath($folders, $parent_id = 0)
    {
        $res = '';
        foreach ($folders as $f) {
            if ($f->parent_id == $parent_id) {
                $res = $f->name . '/' . $this->buildPath($folders, $f->id);
            }
        }
        return $res;
    }
}