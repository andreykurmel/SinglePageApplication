<?php

namespace Vanguard\Services\Tablda\Permissions;


use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Table\Table;
use Vanguard\Services\Tablda\HelperService;

class CustomPermission
{
    public $can_add = 0;
    public $can_download = "0000000";
    public $can_see_history = 0;
    public $can_create_view = 0;
    public $can_create_condformat = 0;
    public $can_see_datatab = 0;
    public $can_edit_tb = 0;
    public $can_drag_rows = 0;
    public $can_drag_columns = 0;
    public $datatab_methods = "000000";
    public $datatab_only_append = 0;
    public $enforced_theme = 0;
    public $view_fields = null;
    public $edit_fields = null;
    public $view_row_groups = null;
    public $edit_row_groups = null;
    public $delete_row_groups = null;
    public $default_values = null;
    public $shared_rows_ids = null;
    public $shared_columns_ids = null;
    public $forbidden_col_settings = null;
    public $_user_is_manager = false;
    public $_addons = null;
    
    protected $service = null;

    /**
     * CustomPermission constructor.
     */
    public function __construct()
    {
        $this->view_fields = collect([]);
        $this->edit_fields = collect([]);
        $this->view_row_groups = collect([]);
        $this->edit_row_groups = collect([]);
        $this->delete_row_groups = collect([]);
        $this->default_values = collect([]);
        $this->shared_rows_ids = collect([]);
        $this->shared_columns_ids = collect([]);
        $this->forbidden_col_settings = collect([]);
        $this->_addons = collect([]);
        
        $this->service = new HelperService();
    }

    /**
     * setTablePermissions
     * @param TablePermission $_permission
     */
    public function setTablePermis(TablePermission $_permission)
    {
        $this->setBasics($_permission);
        $this->setEditPermissions($_permission);
        $this->setAdditionals($_permission);
    }

    /**
     * clearNotUniques
     */
    public function clearNotUniques()
    {
        $this->forbidden_col_settings = $this->forbidden_col_settings->unique()->values();

        $this->edit_fields = $this->edit_fields->unique()->values();
        $this->view_fields = $this->view_fields->unique()->values();

        $this->delete_row_groups = $this->delete_row_groups->unique()->values();
        $this->edit_row_groups = $this->edit_row_groups->unique()->values();
        $this->view_row_groups = $this->view_row_groups->unique()->values();
    }

    /**
     * @param TablePermission $_permission
     */
    protected function setBasics(TablePermission $_permission)
    {
        $this->can_add = (int)($this->can_add || $_permission->can_add);
        $this->can_download = $this->service->mergeByteStrings($this->can_download, $_permission->can_download);
        $this->can_see_history = (int)($this->can_see_history || $_permission->can_see_history);
        $this->can_create_view = (int)($this->can_create_view || $_permission->can_create_view);
        $this->can_create_condformat = (int)($this->can_create_condformat || $_permission->can_create_condformat);
        $this->can_see_datatab = (int)($this->can_see_datatab || $_permission->can_see_datatab);
        $this->can_edit_tb = (int)($this->can_edit_tb || $_permission->can_edit_tb);
        $this->can_drag_rows = (int)($this->can_drag_rows || $_permission->can_drag_rows);
        $this->can_drag_columns = (int)($this->can_drag_columns || $_permission->can_drag_columns);
        $this->enforced_theme = (int)($this->enforced_theme || $_permission->enforced_theme);
        $this->datatab_methods = $this->service->mergeByteStrings($this->datatab_methods, $_permission->datatab_methods);
        $this->datatab_only_append = (int)(
            ($this->datatab_only_append && $this->can_see_datatab)
            ||
            ($_permission->datatab_only_append && $_permission->can_see_datatab)
        );
    }

    /**
     * @param TablePermission $_permission
     */
    protected function setEditPermissions(TablePermission $_permission)
    {
        $view_rows = $_permission->_row_groups->where('_link.view', 1);
        $edit_rows = $_permission->_row_groups->where('_link.edit', 1);
        $delete_rows = $_permission->_row_groups->where('_link.delete', 1);
        $view_columns = $_permission->_column_groups->where('_link.view', 1);
        $edit_columns = $_permission->_column_groups->where('_link.edit', 1);

        $user_is_manager = $_permission->_shared_tables->count();
        $this->_user_is_manager = $this->_user_is_manager || $user_is_manager;
        //$has_group_rows = $edit_rows->where('_is_group_ref_condition', '!=', null)->count();

        //get rights for columns
        //edit
        foreach ($edit_columns as $edit_col_group) {
            $this->edit_fields = $this->edit_fields->merge( $edit_col_group->_fields->pluck('field') );
        }
        //view
        foreach ($view_columns as $view_col_group) {
            $this->view_fields = $this->view_fields->merge( $view_col_group->_fields->pluck('field') );
        }

        //get rights for rows
        //delete
        foreach ($delete_rows as $delete_row_group) {
            $this->delete_row_groups[] = $delete_row_group->id;
        }
        //edit
        foreach ($edit_rows as $edit_row_group) {
            $this->edit_row_groups[] = $edit_row_group->id;
        }
        //view
        foreach ($view_rows as $edit_row_group) {
            if ($user_is_manager && $edit_row_group->_is_group_ref_condition) {
                $this->edit_row_groups[] = $edit_row_group->id;
            }
        }
        //
        $this->view_row_groups = $this->view_row_groups->merge( $view_rows->pluck('id') );
    }

    /**
     * @param TablePermission $_permission
     */
    protected function setAdditionals(TablePermission $_permission)
    {
        //add default values
        foreach ($_permission->_default_fields as $df) {
            $this->default_values = $this->default_values->merge( [$df->_field->field => $df->default] );
        }

        //get shared groups ids
        foreach ($_permission->_column_groups as $cg) {
            if ($cg->_link->shared) {
                $this->shared_columns_ids->push($cg->id);
            }
        }
        foreach ($_permission->_row_groups as $rg) {
            if ($rg->_link->shared) {
                $this->shared_rows_ids->push($rg->id);
            }
        }

        //add forbidden settings columns
        $this->forbidden_col_settings = $this->forbidden_col_settings
            ->merge( $_permission->_forbid_settings->pluck('db_col_name') );

        //add available Addons
        $this->_addons = $this->_addons->merge( $_permission->_addons );
    }
}