<?php

namespace Vanguard\Singletones;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\Folder;
use Vanguard\Modules\MenuTreeModule;
use Vanguard\User;

class AuthUserModule implements AuthUserSingleton
{
    public $user; //Vanguard\User model
    protected $user_groups_edit_allowed_ids;
    protected $user_groups_member;
    protected $table_permission_ids_member;

    protected $rejected_ids;
    protected $shared_tables_ids;
    protected $apps_tables_ids;
    protected $all_tables_shared_ids;
    protected $all_tables_shared;

    protected $apps_folder;
    protected $menu_tree;

    protected $menuTreeModule;

    /**
     * AuthUserModule constructor.
     */
    public function __construct()
    {
        $this->user = auth()->id()
            ? User::find(auth()->id())
            : new User();

        $this->menuTreeModule = new MenuTreeModule($this);
    }

    /**
     * @return Folder
     */
    public function getAppsFolder()
    {
        if (!$this->apps_folder) {
            $this->apps_folder =
                Folder::where("user_id", $this->user->id)
                    ->where("is_system", 1)
                    ->where("name", 'APPs')
                    ->first()
                    ?:
                    new Folder();
        }
        return $this->apps_folder;
    }

    /**
     * @param bool $attach_user_id
     * @param int|null $filter_table_id
     * @return array
     */
    public function getUserIdAndUnderscoreGroups(bool $attach_user_id = true, int $filter_table_id = null)
    {
        //Usergroups
        $ug_ids = $this->getUserGroupsMember($filter_table_id)
            ->pluck('id')
            ->toArray();

        foreach ($ug_ids as &$id) {
            $id = '_' . $id;
        }

        //User id
        if ($attach_user_id) {
            $ug_ids[] = (string)$this->user->id;
        }

        return $ug_ids;
    }

    /**
     * @param int|null $filter_table_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getUserGroupsMember(int $filter_table_id = null)
    {
        if (!$this->user_groups_member) {
            $this->user_groups_member = $this->user
                ->_member_of_groups()
                ->with([
                    '_tables_shared' => function ($q) {
                        $q->where('is_active', 1);
                    }
                ])
                ->get();
        }
        return !$filter_table_id
            ? $this->user_groups_member
            : $this->user_groups_member->filter(function ($el) use ($filter_table_id) {
                return $el->_tables_shared->where('table_id', '=', $filter_table_id)->count();
            });
    }

    /**
     * @return Collection
     */
    public function getIdsUserGroupsEditAdded()
    {
        if (!$this->user_groups_edit_allowed_ids) {
            $this->user_groups_edit_allowed_ids = $this->getUserGroupsMember()
                ->where('_link.is_edit_added', 1)
                ->pluck('id');
            $this->user_groups_edit_allowed_ids[] = 0;
        }
        return $this->user_groups_edit_allowed_ids;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getTablePermissionIdsMember()
    {
        if (!$this->table_permission_ids_member) {
            $ug_ids = $this->getUserGroupsMember()->pluck('id');
            $this->table_permission_ids_member = TablePermission::whereHas('_user_groups', function ($ug) use ($ug_ids) {
                    $ug->where('user_groups_2_table_permissions.is_active', 1);
                    $ug->whereIn('user_groups_2_table_permissions.user_group_id', $ug_ids);
                })
                ->select('id')
                ->get()
                ->pluck('id');
            //none of Permissions
            $this->table_permission_ids_member[] = '';
        }
        return $this->table_permission_ids_member;
    }

    /**
     * @param string $type
     * @return Collection
     */
    public function sharedTablesIds(string $type = 'all')
    {
        switch ($type) {
            //tables for 'SHARED' folder.
            case 'shared': return $this->getTablesIdsInSharedFolder();
            //tables for 'APPs' folder.
            case 'apps': return $this->getTablesIdsInAppsFolder();
            //tables for 'APPs' and 'SHARED' folder.
            default: return $this->getAllSharedTablesIds();
        }
    }

    /**
     * @return Collection
     */
    protected function getAllSharedTables()
    {
        if (!$this->all_tables_shared) {
            $this->all_tables_shared = $this->getUserGroupsMember()
                ->pluck('_tables_shared')
                ->flatten();
        }
        return $this->all_tables_shared;
    }

    /**
     * @return Collection
     */
    protected function getAllSharedTablesIds()
    {
        return $this->getAllSharedTables()->pluck('table_id');
    }

    /**
     * @return Collection
     */
    protected function getTablesIdsInAppsFolder()
    {
        return $this->getAllSharedTables()
            ->where('is_app', 1)
            ->pluck('table_id');
    }

    /**
     * @return Collection
     */
    protected function getRejectedTablesIds()
    {
        if (!$this->rejected_ids) {
            $this->rejected_ids = DB::table('users_u_groups_rejected_tables')
                ->whereIn('user_group_id', $this->getUserGroupsMember()->pluck('id'))
                ->where('user_id', $this->user->id)
                ->select('table_id')
                ->get()
                ->pluck('table_id');
        }
        return $this->rejected_ids;
    }

    /**
     * @return Collection
     */
    protected function getTablesIdsInSharedFolder()
    {
        return $this->getAllSharedTables()
            ->where('is_app', 0)
            ->pluck('table_id');
    }

    /**
     * Get Folder with structure view for js-tree.
     *
     * @param $folder_id
     * @return array
     */
    public function getMenuTreeFolder(int $folder_id)
    {
        $menutree = $this->getMenuTree();
        return $this->menuTreeModule->findInTree($menutree['private'] ?? [], $folder_id);
    }

    /**
     * Get Folders and Tables available to User in structure view for js-tree.
     *
     * @return array
     */
    public function getMenuTree()
    {
        if (!$this->menu_tree) {
            $this->menu_tree = $this->menuTreeModule->build();
        }
        return $this->menu_tree;
    }

    /**
     * @param int $table_id
     * @param string $table_name
     * @return string
     */
    public function getTableUrl(int $table_id, string $table_name)
    {
        $menu = $this->getMenuTree();
        foreach ($menu as $part) {
            $tb_node = $this->menuTreeModule->findInTree($part ?? [], $table_id, 'table');
            if ($tb_node) {
                return $tb_node['a_attr']['href'] . $table_name;
            }
        }
        return '';
    }
}