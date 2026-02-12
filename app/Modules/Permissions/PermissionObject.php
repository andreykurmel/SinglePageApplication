<?php

namespace Vanguard\Modules\Permissions;


use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\Singletones\OtherUserModule;

class PermissionObject
{
    public $permis_ids = [];
    public $is_owner = 0;//present 'user_id' if owner
    public $can_add = 0;
    public $can_download = "0000000";
    public $can_see_history = 0;
    public $can_create_view = 0;
    public $can_create_condformat = 0;
    public $can_see_datatab = 0;
    public $can_edit_tb = 0;
    public $can_drag_rows = 0;
    public $can_drag_columns = 0;
    public $can_change_primaryview = 0;
    public $can_reference = 0;
    public $datatab_methods = "000000";
    public $datatab_only_append = 0;
    public $enforced_theme = 0;

    public $linked_view_fields = [];//
    public $view_fields = [];//collection of 'field'
    public $edit_fields = [];//collection of 'field'

    public $view_col_groups = [];//collection of 'id'
    public $edit_col_groups = [];//collection of 'id'
    public $shared_col_groups = [];//collection of 'id'
    public $view_row_groups = [];//collection of 'id'
    public $edit_row_groups = [];//collection of 'id'
    public $delete_row_groups = [];//collection of 'id'
    public $shared_row_groups = [];//collection of 'id'

    public $default_values = [];
    public $shared_rows_ids = [];
    public $shared_columns_ids = [];
    public $forbidden_col_settings = [];
    public $_manager_of_ugroups = [];
    public $_addons = [];
    
    protected $service = null;

    /**
     * @param int|null $user_id
     * @param int $is_owner
     */
    public function __construct(int $user_id = null, int $is_owner = 0)
    {
        $this->is_owner = $is_owner;
        $this->view_fields = collect([]);
        $this->edit_fields = collect([]);
        $this->linked_view_fields = collect([]);

        $this->view_col_groups = collect([]);
        $this->edit_col_groups = collect([]);
        $this->shared_col_groups = collect([]);
        $this->view_row_groups = collect([]);
        $this->edit_row_groups = collect([]);
        $this->delete_row_groups = collect([]);
        $this->shared_row_groups = collect([]);

        $this->default_values = collect([]);
        $this->shared_rows_ids = collect([]);
        $this->shared_columns_ids = collect([]);
        $this->forbidden_col_settings = collect([]);
        $this->_addons = collect([]);

        $this->_manager_of_ugroups = (new OtherUserModule($user_id))->getManagerOfUserGroups(true);
        
        $this->service = new HelperService();
    }

    /**
     * @param TableFieldLink $fieldLink
     * @return void
     */
    public function setLinkedColumns(TableFieldLink $fieldLink): void
    {
        $key = $fieldLink->__inlined ? 'in_inline_display' : 'in_popup_display';

        $this->linked_view_fields = collect([''])->merge(
            $fieldLink->_columns->where($key, '=', 1)->pluck('field_db')
        );
    }

    /**
     * @param Table $table
     * @param $tableRow
     * @return bool
     */
    public function managerOfRow(Table $table, $tableRow): bool
    {
        $ugroups = $this->_manager_of_ugroups->toArray();
        if ($ugroups) {
            $found = false;
            foreach($table->_fields as $header) {
                if ($header->f_type === 'User' && $tableRow[$header->field]) {
                    $single = in_array($tableRow[$header->field], $ugroups);
                    $multiple = false;
                    foreach($ugroups as $ug) {
                        $multiple = $multiple || strpos($tableRow[$header->field], '"'.$ug.'"');
                    };
                    $found = $found || $single || $multiple;
                }
            };
            return $found;
        } else {
            return false;
        }
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

        $this->shared_col_groups = $this->shared_col_groups->unique()->values();
        $this->edit_col_groups = $this->edit_col_groups->unique()->values();
        $this->view_col_groups = $this->view_col_groups->unique()->values();

        $this->shared_row_groups = $this->shared_row_groups->unique()->values();
        $this->delete_row_groups = $this->delete_row_groups->unique()->values();
        $this->edit_row_groups = $this->edit_row_groups->unique()->values();
        $this->view_row_groups = $this->view_row_groups->unique()->values();
    }

    /**
     * @param TablePermission $_permission
     */
    protected function setBasics(TablePermission $_permission)
    {
        $this->permis_ids[] = $_permission->id;
        $this->can_add = (int)($this->can_add || $_permission->can_add);
        $this->can_download = $this->service->mergeByteStrings($this->can_download, $_permission->can_download);
        $this->can_see_history = (int)($this->can_see_history || $_permission->can_see_history);
        $this->can_create_view = (int)($this->can_create_view || $_permission->can_create_view);
        $this->can_create_condformat = (int)($this->can_create_condformat || $_permission->can_create_condformat);
        $this->can_see_datatab = (int)($this->can_see_datatab || $_permission->can_see_datatab);
        $this->can_edit_tb = (int)($this->can_edit_tb || $_permission->can_edit_tb);
        $this->can_drag_rows = (int)($this->can_drag_rows || $_permission->can_drag_rows);
        $this->can_drag_columns = (int)($this->can_drag_columns || $_permission->can_drag_columns);
        $this->can_change_primaryview = (int)($this->can_change_primaryview || $_permission->can_change_primaryview);
        $this->enforced_theme = (int)($this->enforced_theme || $_permission->enforced_theme);
        $this->can_reference = (int)($this->can_reference || $_permission->can_reference);
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
        $share_rows = $_permission->_row_groups->where('_link.shared', 1);
        $delete_rows = $_permission->_row_groups->where('_link.delete', 1);
        $view_columns = $_permission->_column_groups->where('_link.view', 1);
        $edit_columns = $_permission->_column_groups->where('_link.edit', 1);
        $shared_columns = $_permission->_column_groups->where('_link.shared', 1);

        //get rights for columns
        //edit
        foreach ($edit_columns as $edit_col_group) {
            $this->edit_fields = $this->edit_fields->merge( $edit_col_group->_fields->pluck('field') );
        }
        //view
        foreach ($view_columns as $view_col_group) {
            $this->view_fields = $this->view_fields->merge( $view_col_group->_fields->pluck('field') );
        }


        //get rights for cols
        //shared
        foreach ($shared_columns as $share_col_group) {
            $this->shared_col_groups[] = $share_col_group->id;
        }
        //edit
        foreach ($edit_columns as $edit_col_group) {
            $this->edit_col_groups[] = $edit_col_group->id;
        }
        //view
        foreach ($view_columns as $edit_col_group) {
            $this->view_col_groups[] = $edit_col_group->id;
        }


        //get rights for rows
        //shared
        foreach ($share_rows as $share_row_group) {
            $this->shared_row_groups[] = $share_row_group->id;
        }
        //delete
        foreach ($delete_rows as $delete_row_group) {
            $this->delete_row_groups[] = $delete_row_group->id;
        }
        //edit
        foreach ($edit_rows as $edit_row_group) {
            $this->edit_row_groups[] = $edit_row_group->id;
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
            $this->default_values = $this->default_values->merge( [
                $df->_field->field => [
                    'input_type' => $df->_field->input_type,
                    'default' => $df->default,
                ]
            ] );
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