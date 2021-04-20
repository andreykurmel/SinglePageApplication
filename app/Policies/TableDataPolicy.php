<?php

namespace Vanguard\Policies;

use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\FolderView;
use Vanguard\Models\Folder\FolderViewTable;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableView;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TableDataPolicy
{
    use HandlesAuthorization;

    private $service;
    private $userPermissionsService;
    private $tableFieldRepository;

    /**
     * TableDataPolicy constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->userPermissionsService = new UserPermissionsService();
        $this->tableFieldRepository = new TableFieldRepository();
    }

    /**
     * @param string $web_hash
     * @return bool
     */
    protected function availViaHash(string $web_hash = '')
    {
        return $web_hash && (
            //table used in TableView
            TableView::where('hash', '=', $web_hash)->where('is_active', '=', 1)->count()
            //table used in FolderView
            || FolderView::where('hash', '=', $web_hash)->where('is_active', '=', 1)->count()
            //avail from DCR
            || TablePermission::where('dcr_hash', '=', $web_hash)->where('active', 1)->count()
            //avail from EmailAddon
            || TableEmailAddonSetting::where('hash', '=', $web_hash)->count()
        );
    }

    /**
     * Authorize that user can load info about table
     *
     * @param User $user
     * @param Table $table
     * @param string $web_hash
     * @return bool
     */
    public function load(User $user, Table $table, string $web_hash = '')
    {
        return $this->isOwner($user, $table)
            //table connected to 'public' folders -> so it is accessible to any user and guest
            || $table->is_public
            //user has right to access this table directly
            || $table->_table_permissions()->where(function($sub) {
                $sub->isActiveForUser();
                $sub->orWhere(function ($q) {
                    $q->where('is_system', 1)->where('can_reference', 1);
                });
            })->count()
            //user get sys tables which is defined 'for all'
            || in_array($table->db_name, $this->service->system_tables_for_all)
            //table used for APPs as public
            || CorrespTable::where('data_table', $table->db_name)->where('options', 'like', '%is_public:true%')->count()
            //table used via View or DCR
            || $this->availViaHash($web_hash);
    }

    /**
     * Authorize that user can view table rows
     *
     * @param User $user
     * @param Table $table
     * @param string $web_hash
     * @return mixed
     */
    public function get(User $user, Table $table, string $web_hash = '')
    {
        if ($table->db_name == 'table_fields__for_tooltips' && $user->id == 1) {
            return true;
        }

        if ($table->is_system && !in_array($table->db_name, $this->service->system_tables_for_all)) {
            return true;
        }

        $userHaveAccess = $this->isOwner($user, $table);
        if (!$userHaveAccess) {
            $userHaveAccess = $table->_table_permissions()
                ->join('table_permissions_2_table_column_groups as pivot', 'pivot.table_permission_id', '=', 'table_permissions.id')
                ->where('pivot.view', '=', 1)
                ->applyIsActiveForUserOrPermission(null, true)
                ->count();
        }
        if (!$userHaveAccess) {
            $userHaveAccess = $this->availViaHash($web_hash);
        }
        return $userHaveAccess;
    }

    /**
     * Authorize that user can insert rows into the table
     *
     * @param User $user
     * @param \Vanguard\Models\Table\Table $table
     * @param string $web_hash
     * @return bool
     */
    public function insert(User $user, Table $table, string $web_hash = '')
    {
        if ($table->is_system == 1 && !in_array($table->db_name, $this->service->support_tables)) {
            return false;
        }

        $userHaveAccess = $this->isOwner($user, $table);
        if (!$userHaveAccess) {
            //user has right to update columns in this table
            $userHaveAccess = $table->_table_permissions()
                ->where('can_add', '=', 1)
                ->applyIsActiveForUserOrPermission(null, true)
                ->count();
        }
        if (!$userHaveAccess) {
            $userHaveAccess = $this->availViaHash($web_hash);
        }
        return $userHaveAccess;
    }

    /**
     * Authorize that user can update table rows
     *
     * @param User $user
     * @param \Vanguard\Models\Table\Table $table
     * @param string $web_hash
     * @return bool
     */
    public function update(User $user, Table $table, string $web_hash = '')
    {
        if ($table->db_name == 'table_fields__for_tooltips' && $user->id == 1) {
            return true;
        }

        if ($table->is_system && !in_array($table->db_name, $this->service->system_tables_for_all)) {
            return false;
        }

        $userHaveAccess = $this->isOwner($user, $table);
        if (!$userHaveAccess) {
            $userHaveAccess = $table->_table_permissions()
                ->join('table_permissions_2_table_column_groups as pivot', 'pivot.table_permission_id', '=', 'table_permissions.id')
                ->where('pivot.edit', '=', 1)
                ->applyIsActiveForUserOrPermission(null, true)
                ->count();
        }
        if (!$userHaveAccess) {
            $userHaveAccess = $this->availViaHash($web_hash);
        }
        return $userHaveAccess;
    }

    /**
     * Authorize that user can delete rows from table
     *
     * @param User $user
     * @param Table $table
     * @param string $web_hash
     * @return bool
     */
    public function delete(User $user, Table $table, string $web_hash = '')
    {
        if ($table->is_system == 1 && !in_array($table->db_name, $this->service->support_tables)) {
            return false;
        }

        $userHaveAccess = $this->isOwner($user, $table);
        if (!$userHaveAccess) {
            //user has right to delete rows from this table
            $userHaveAccess = $table->_table_permissions()
                ->join('table_permissions_2_table_row_groups as pivot', 'pivot.table_permission_id', '=', 'table_permissions.id')
                ->where('pivot.delete', '=', 1)
                ->applyIsActiveForUserOrPermission(null, true)
                ->count();
        }
        if (!$userHaveAccess) {
            $userHaveAccess = $this->availViaHash($web_hash);
        }
        return $userHaveAccess;
    }

    /**
     * User can do all actions if he is table owner
     * Or User is Admin.
     *
     * @param User $user
     * @param \Vanguard\Models\Table\Table $table
     * @return bool
     */
    public function isOwner(User $user, Table $table) {
        return ($user->id && $table->user_id == $user->id)
            || (auth()->user() && auth()->user()->isAdmin()); //or Admin
    }

    /**
     * User can modify structure of the Table.
     *
     * @param User $user
     * @param \Vanguard\Models\Table\Table $table
     * @return bool
     */
    public function modifyTable(User $user, Table $table) {
        if ($table->is_system) {
            return false;
        }

        $userHaveAccess = $this->isOwner($user, $table);
        if (!$userHaveAccess) {
            //user has right to delete columns from this table
            $userHaveAccess = $table->_table_permissions()
                ->where('can_see_datatab', '=', 1)
                ->applyIsActiveForUserOrPermission()
                ->count();
        }
        return $userHaveAccess;
    }
}
