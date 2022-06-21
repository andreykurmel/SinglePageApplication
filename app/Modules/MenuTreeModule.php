<?php

namespace Vanguard\Modules;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\Models\Folder\FolderStructure;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserModule;
use Vanguard\User;

class MenuTreeModule
{
    private $user_id;
    private $AuthUserModule;
    private $service;
    private $tabs = ['public', 'private', 'favorite', 'account'];

    /**
     * MenuTreeModule constructor.
     * @param $AuthUserModule
     */
    public function __construct(AuthUserModule $AuthUserModule)
    {
        $this->AuthUserModule = $AuthUserModule;
        $this->user_id = $AuthUserModule->user->id;
        $this->service = new HelperService();
    }

    /**
     * Find Folder in menutree.
     *
     * @param array $menutree
     * @param $compare_val
     * @param string $type
     * @return array
     */
    public function findInTree(array $menutree, $compare_val, $type = 'folder')
    {
        foreach ($menutree as &$node) {
            //search in children
            if ($node['children']) {
                $res = $this->findInTree($node['children'], $compare_val, $type);
                if ($res) {
                    return $res;
                }
            }

            $found = $type == 'folder'
                ? $node['li_attr']['data-id'] == $compare_val //by folder id
                : in_array($compare_val, array_pluck($node['li_attr']['data-object']['_tables'] ?? [], 'id')); // by table id
            if ($node['li_attr']['data-type'] == 'folder' && $found) {
                return $node;
            }
        }

        return [];
    }

    /**
     * Load Folders and Build them for jsTree.
     *
     * @return array [
     *  'public' => [array for jstree],
     *  'private' => [array for jstree],
     *  'favorite' => [array for jstree],
     *  'account' => [array for jstree],
     * ]
     */
    public function build()
    {
        $this->clearOldSharedForUserFolders();

        $folders = $this->loadFoldersWithTables(false);
        $folders = $this->setAllTabs($folders);
        $root_tables = $this->getRootTablesForMenu();
        $res = [];
        foreach ($folders as $tab => $tabFolders) {
            //not-logged user can see only 'public'
            if (!$this->user_id && $tab != 'public') {
                continue;
            }

            //attach shared root tables to each shared User folder.
            if (!empty($root_tables[$tab])) {
                $tabFolders = $this->attachSharedRootTables($tabFolders, $root_tables[$tab]);
            }

            //create menutree for ['public','private','favorite','account'] tabs.
            $root_link = $this->service->getUsersUrl($this->AuthUserModule->user, $tab) . '/data/';
            $res[$tab] = $this->buildFolderTree($tabFolders, $tab, $root_link);
            $res[$tab] = $res[$tab]['tree'];

            //attach 'APPs' folder with shared tables to 'private' menutree.
            if ($tab == 'private') {
                $app_folders = $this->loadFoldersWithTables(true);
                $app_tree = $this->buildFolderTree($app_folders[$tab], $tab, $root_link);
                $res[$tab] = array_merge($res[$tab], $app_tree['tree']);
            }

            //attach root tables to each menutree tab.
            if (!empty($root_tables[$tab])) {
                $res[$tab] = $this->addRootTablesToTree($res[$tab], $root_tables[$tab], $root_link, $tab);
            }
        }
        return $res;
    }

    /**
     * Load owned, public and 'shared' Folders.
     * OR
     * Load 'apps' Folders.
     *
     * @param bool $is_app
     * @return Collection of Folder grouped by 'structure' ('public','private','favorite','account')
     */
    protected function loadFoldersWithTables($is_app = false)
    {
        $folders_table = (new Folder())->getTable();
        $AuthUserModule = $this->AuthUserModule;

        $folders = Folder::joinFolderStructure()->with($this->withTablesRelation($is_app));

        if ($is_app) {
            $folders = $folders
                //owned folder 'APPs'
                ->where("$folders_table.id", $AuthUserModule->getAppsFolder()->id)
                //owned folders with 'username' in 'APPs'
                ->orWhere(function ($q) use ($folders_table, $AuthUserModule) {
                    $q->where("$folders_table.user_id", $AuthUserModule->user->id);
                    $q->where("$folders_table.in_shared", '=', 2);
                })
                //shared folders in 'APPs'
                ->orWhereIn("$folders_table.id", $this->getSharedFoldersIds(true));
        } else {
            $folders = $folders
                //public folders (and some 'Account' folders)
                ->whereNull("$folders_table.user_id")
                //owned folders no system 'APPs'
                ->orWhere(function ($q) use ($folders_table, $AuthUserModule) {
                    $q->where("$folders_table.user_id", $AuthUserModule->user->id);
                    $q->where("$folders_table.id", '!=', $AuthUserModule->getAppsFolder()->id);
                })
                //shared folders in 'SHARED'
                ->orWhereIn("$folders_table.id", $this->getSharedFoldersIds(false));
        }

        $folders = $folders->get()->groupBy('structure');
        foreach ($folders as $key => $folderTab) {
            //link shared folders
            if ($key == 'private') {
                $folderTab = $this->linkSharedFolders($folderTab, $is_app);
            } else {
                $this->fillTableParams($folderTab);
            }

            //sort by 'is_system', 'menutree_order', 'name'.
            $folders[$key] = $folderTab->sort(function ($a, $b) {
                    if ($a->is_system == $b->is_system) {
                        if ($a->menutree_order == $b->menutree_order) {
                            return $a->name <=> $b->name;
                        }
                        return $a->menutree_order <=> $b->menutree_order;
                    }
                    return $a->is_system <=> $b->is_system;
                })
                ->values();
        }

        return $folders;
    }

    /**
     * @param Collection $folderTab
     */
    protected function fillTableParams(Collection $folderTab)
    {
        foreach ($folderTab as $folder) {
            foreach ($folder->_tables as $table) {
                $table->_permis_can_public_copy = $table->_table_permissions->where('can_public_copy', '=', 1)->count();
                $table->_permis_hide_folder_structure = $table->_table_permissions->where('hide_folder_structure', '=', 1)->count();
                $folder->_hide_structure = $table->_permis_hide_folder_structure;
                unset($table->_table_permissions);
            }
        }
    }

    /**
     * Apply '_tables' relation.
     *
     * @param bool $is_app
     * @return array
     */
    protected function withTablesRelation($is_app = false)
    {
        $AuthUserModule = $this->AuthUserModule;
        $admin_supports = $this->service->admin_support;

        return [
            //get owned or shared tables
            '_tables' => function ($t) use ($AuthUserModule, $is_app, $admin_supports) {
                $tables_tb = (new Table())->getTable();
                $pivots = (new Folder2Table())->getTable();

                //conditions
                if (!$AuthUserModule->user->isAdmin()) {
                    $t->whereNotIn("$tables_tb.db_name", $admin_supports);
                }
                $t->where(function ($inner) use ($tables_tb, $pivots, $AuthUserModule, $is_app) {
                    $inner->where("$pivots.structure", '!=', 'private');
                    $inner->orWhere("$tables_tb.user_id", '=', $AuthUserModule->user->id);
                    $inner->orWhereIn("$tables_tb.id", $AuthUserModule->sharedTablesIds($is_app ? 'apps' : 'shared'));
                });

                //order
                $t->orderBy("$tables_tb.menutree_order");
                $t->orderBy("$tables_tb.name");

                $t->with([
                    '_shared_names' => function ($sn) use ($AuthUserModule) {
                        $sn->where('user_id', '=', $AuthUserModule->user->id);
                    },
                    '_table_permissions' => function ($tb) {
                        $tb->where(function ($sub) {
                            $sub->isActiveForUserOrVisitor();
                        });
                        $tb->where(function ($sub) {
                            $sub->where('can_public_copy', '=', 1);
                            $sub->orWhere('hide_folder_structure', '=', 1);
                        });
                    },
                ]);
            }
        ];
    }

    /**
     * Get Folders with their parents
     * Based on shared tables.
     *
     * @param bool $is_app
     * @return array
     */
    protected function getSharedFoldersIds($is_app = false)
    {
        //get shared tables
        $shared_tables = $this->AuthUserModule->sharedTablesIds($is_app ? 'apps' : 'shared');

        $folders_table = (new Folder())->getTable();
        $structure_table = (new FolderStructure())->getTable();
        $f2_table = (new Folder2Table())->getTable();

        //get folders for shared tables
        $folders_ids = Folder::joinFolder2Table()
            ->whereIn("$f2_table.table_id", $shared_tables->unique())
            ->where("$f2_table.type", 'table')
            ->select("$folders_table.id")
            ->get()
            ->pluck('id');

        //get parent folders for selected folders
        return FolderStructure::whereIn("child_id", $folders_ids->unique())
            ->select("element_id")
            ->get()
            ->pluck('element_id')
            ->unique()
            ->toArray();
    }

    /**
     * Correct parent id for shared Folders.
     *
     * @param Collection $folderTab
     * @param bool $is_app
     * @return Collection of Folder
     */
    protected function linkSharedFolders(Collection $folderTab, $is_app = false)
    {
        $share_folder = $folderTab->where('is_system', '=', 1)
            ->where('user_id', '=', $this->user_id)
            ->where('name', '=', ($is_app ? 'APPs' : 'SHARED'))
            ->first();

        $folderTab = $this->createSharedForUsersFolders($folderTab, $is_app);
        $this->fillTableParams($folderTab);

        //Note: is User shared folders to self as 'Is App' -> system should put this folders to 'App' shared folder.
        $shared_ids = $is_app ? $this->getSharedFoldersIds(true) : [];
        //If shared folders to self are not 'Is App' -> folders will not showed in 'SHARED' folder.

        $folder_hide = [];
        foreach ($folderTab as $fld) {
            if ($fld->user_id != $this->user_id || in_array($fld->id, $shared_ids)) {
                $fld->in_shared = ($is_app ? 2 : 1);

                //Folder with username from which data is shared
                $shared_user_folder = $folderTab->where('for_shared_user_id', '=', $fld->user_id)
                    ->where('in_shared', '=', $share_folder->in_shared)
                    ->first();

                //link to Folder with shared user name.
                if (!$fld->parent_id) {
                    $fld->parent_id = $shared_user_folder ? $shared_user_folder->id : 0;
                }

                //fix link names and additional permissions
                foreach ($fld->_tables as $tb) {
                    $tb->name = $tb->_shared_names->count() ? $tb->_shared_names[0]->name : $tb->name;
                    $tb->_in_shared = ($is_app ? 2 : 1);
                    $tb->_in_app = ($is_app ? 1 : 0);
                }

                //Show shared tables without folder structure
                if ($fld->_hide_structure) {
                    foreach ($fld->_tables as $tb) {
                        $shared_user_folder->_tables
                            ? $shared_user_folder->_tables->push($tb)
                            : $shared_user_folder->_tables = collect([$tb]);
                    }
                    $folder_hide[] = $fld->id;
                }

            }
        }

        //remove all parent folders from 'hidden shared folders'
        while ($folder_hide) {
            $parents = $folderTab->whereIn('id', $folder_hide)
                ->pluck('parent_id')
                ->toArray();
            $folderTab = $folderTab->filter(function ($fold) use ($folder_hide) {
                return $fold->user_id == $this->user_id || !in_array($fold->id, $folder_hide);
            });
            $folder_hide = $parents;
        }

        return $folderTab;
    }

    /**
     * Delete shared user folders without tables
     * AND insert shared user folders with new tables.
     *
     * @param Collection $folderTab
     * @param bool $is_app
     * @return Collection of Folder
     */
    protected function createSharedForUsersFolders(Collection $folderTab, $is_app = false)
    {
        $share_folder = $folderTab->where('is_system', 1)
            ->where('user_id', '=', $this->user_id)
            ->where('name', '=', ($is_app ? 'APPs' : 'SHARED'))
            ->first();

        $shared_user_ids = $this->AuthUserModule->getUserGroupsMember()
            ->filter(function ($ug) use ($is_app) {
                return $ug->_tables_shared->where('is_app', '=', ($is_app ? 1 : 0))->count();
            })
            ->pluck('user_id')
            ->unique();

        $present_user_ids = $folderTab->where('for_shared_user_id', '!=', null)
            ->where('in_shared', '=', $share_folder->in_shared)
            ->pluck('for_shared_user_id')
            ->unique();

        //add needed folders
        $not_added_user_ids = $shared_user_ids->diff($present_user_ids);
        if ($not_added_user_ids->count() && $share_folder) {
            $folderTab = $folderTab->merge( $this->createFolders($not_added_user_ids, $share_folder) );
        }

        //delete already not shared folders
        $extra_user_ids = $present_user_ids->diff($shared_user_ids);
        if ($extra_user_ids->count()) {
            Folder::where('user_id', '=', $this->user_id)
                ->whereIn('for_shared_user_id', $extra_user_ids)
                ->where('in_shared', '=', $share_folder->in_shared)
                ->delete();
            $folderTab = $folderTab->filter(function ($fld) use ($extra_user_ids) {
                return !$extra_user_ids->contains($fld->for_shared_user_id);
            });
        }

        return $folderTab;
    }

    /**
     * Remove shared folders which are old (for update names)
     */
    protected function clearOldSharedForUserFolders()
    {
        Folder::where('user_id', '=', $this->user_id)
            ->whereNotNull('for_shared_user_id')
            ->where('created_on', '<', Carbon::now()->subDay(1))
            ->delete();
    }

    /**
     * @param $not_added_user_ids
     * @param Folder $share_folder
     * @return Folder Collection
     */
    protected function createFolders($not_added_user_ids, Folder $share_folder)
    {
        $repo = new FolderRepository();
        $users = User::whereIn('id', $not_added_user_ids)->get();
        $added_ids = [];
        foreach ($not_added_user_ids as $shared_user_id) {
            $usr = $users->where('id', '=', $shared_user_id)->first();
            $f_name = '@' . ($usr->first_name ? $usr->first_name.' '.$usr->last_name : $usr->username);
            $f = $repo->insertFolder($share_folder->id, $this->user_id, $f_name, 'private', [
                'in_shared' => $share_folder->in_shared,
                'for_shared_user_id' => $shared_user_id
            ]);
            $added_ids[] = $f->id;
        }
        $folders_table = (new Folder())->getTable();
        return Folder::joinFolderStructure()->whereIn("$folders_table.id", $added_ids)->get();
    }

    /**
     * Set all needed tabs.
     *
     * @param Collection $folders
     * @return Collection
     */
    protected function setAllTabs(Collection $folders)
    {
        foreach ($this->tabs as $tab) {
            if (empty($folders[$tab])) {
                $folders[$tab] = new Collection([]);
            }
        }
        return $folders;
    }

    /**
     * Get Root Tables to build MenuTree
     *
     * @return Collection of Table grouped by 'structure' ('public','private','favorite','account')
     */
    protected function getRootTablesForMenu()
    {
        $tb_table = (new Table())->getTable();
        $f2_table = (new Folder2Table())->getTable();
        $AuthUserModule = $this->AuthUserModule;

        $root_tables = (new Folder())
            ->_tables()
            //owned root Tables
            ->where("$tb_table.user_id", '=', $this->user_id)
            //shared root Tables
            ->orWhere(function ($q) use ($tb_table, $f2_table, $AuthUserModule) {
                $q->whereIn("$tb_table.id", $AuthUserModule->sharedTablesIds('shared'));
                $q->where("$f2_table.folder_id", '=', null);
            })
            //has in favorite
            ->orWhere(function ($q) use ($tb_table, $f2_table, $AuthUserModule) {
                $q->where("$f2_table.user_id", '=', $AuthUserModule->user->id);
                $q->where("$f2_table.folder_id", '=', null);
                $q->where("$f2_table.structure", '=', 'favorite');
            })
            ->get();

        $root_tables = $root_tables->groupBy('link.structure');
        foreach ($root_tables as $key => $tableTab) {
            //sort by 'menutree_order', 'name'.
            $root_tables[$key] = $tableTab
                ->sort(function ($a, $b) {
                    if ($a->menutree_order == $b->menutree_order) {
                        return $a->name <=> $b->name;
                    }
                    return $a->menutree_order <=> $b->menutree_order;
                })
                ->values();
        }

        //add forbidden owned tables
        $root_tables['private'] = Table::where('user_id', '=', $this->user_id)
            ->whereNotIn('id', HelperService::sysTbIds())
            ->whereDoesntHave('_folders')
            ->get()
            ->map(function ($tb) {
                $link = new Folder2Table();
                $link->table_id = $tb->id;
                $link->user_id = $tb->user_id;
                $link->type = 'table';
                $link->structure = 'private';
                $tb->link = $link;
                return $tb;
            })
            ->merge($root_tables['private'] ?? collect([]));

        return $root_tables;
    }

    /**
     * Attach shared root tables to tree.
     *
     * @param Collection $folders
     * @param $tables
     * @return Collection
     */
    protected function attachSharedRootTables(Collection $folders, $tables)
    {
        $shared_tables = $tables->where('user_id', '!=', $this->user_id)->groupBy('user_id');

        foreach ($shared_tables as $user_id => $tables_by_user) {
            foreach ($folders as &$share_folder) {
                if ($share_folder->for_shared_user_id == $user_id) {
                    foreach ($tables_by_user as $tb) {
                        $tb->link->type = 'share-alt';
                        $share_folder->_tables->push($tb);
                    }
                }
            }
        }
        return $folders;
    }

    /**
     * Create tree with folders and tables for user.
     * Tree elements are compatible with js-tree:
     * {
     *  text: "string" // node text
     *  icon: "string" // string for custom
     *  state       : {
     *      opened    : boolean  // is the node open
     *      disabled  : boolean  // is the node disabled
     *      selected  : boolean  // is the node selected
     *  },
     *  children    : []  // array of strings or objects
     *  li_attr     : {}  // attributes for the generated LI node
     *  a_attr      : {}  // attributes for the generated A node
     * }
     *
     * @param $elements
     * @param $tab
     * @param $link
     * @param int $parentId
     * @return array
     */
    protected function buildFolderTree($elements, $tab, $link, $parentId = 0)
    {
        $branch = [
            'tree' => [],
            'folders' => 0,
            'tables' => 0
        ];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $folder_link = $link . $element->name . '/';

                $children = $this->buildFolderTree($elements, $tab, $folder_link, $element->id);
                $children['tables'] += count($element->_tables);

                //mark folders in 'SHARED' for which user is owner.
                $folder_class = ($element->in_shared && $element->user_id == $this->user_id && $element->name != 'SHARED') ? 'user-available-link' : '';

                $children['sub_tables'] = $this->generateTablesElements($element->id, $element->_tables, $folder_link, $tab);
                $obj = $this->service->getFolderObjectForMenuTree($element, $children, $folder_link, false, $folder_class);

                $branch['tree'][] = $obj;
                $branch['folders'] += 1 + $children['folders'];
                $branch['tables'] += $children['tables'];
            }
        }

        return $branch;
    }

    /**
     * Add tables to folder in the tree.
     *
     * @param $parent_folder_id
     * @param $tables
     * @param $path
     * @param $tab
     * @return array
     */
    protected function generateTablesElements($parent_folder_id, $tables, $path, $tab)
    {
        $arr = [];
        foreach ($tables as $table) {
            if ($tab != 'public' || !$table->pub_hidden) {
                $link_class = ($tab == 'public' && $this->user_id == $table->user_id) ? 'user-available-link' : '';
                $link_type = $this->getLinkType($table);
                $arr[] = $this->service->getTableObjectForMenuTree($table, $link_type, $path, $parent_folder_id, $link_class);
            }
        }
        return $arr;
    }

    /**
     * Get link type for table.
     *
     * @param $table
     * @return string
     */
    protected function getLinkType($table)
    {
        if ($table->_in_app) {
            return 'cloud';
        } elseif ($table->_in_shared) {
            return 'share-alt';
        } elseif ($table->link) {
            return $table->link->type;
        } else {
            return 'link';
        }
    }

    /**
     * Attach owned root tables to tree tab.
     *
     * @param array $menutree_tab
     * @param $tables
     * @param $root_link
     * @param $tab
     * @return array
     */
    protected function addRootTablesToTree(array $menutree_tab, $tables, $root_link, $tab)
    {
        //$owned_tables = $tables->where('user_id', $this->user_id);
        return array_merge(
            $this->generateTablesElements(null, $tables, $root_link, $tab),
            $menutree_tab
        );
    }
}