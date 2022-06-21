<?php


namespace Vanguard\Services\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Classes\SysColumnCreator;
use Vanguard\Jobs\AutoFillAllDdls;
use Vanguard\Jobs\FillMirrorValues;
use Vanguard\Jobs\WatchRemoteFiles;
use Vanguard\Models\DDL;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;

class TableFieldService
{
    protected $fieldLinkRepository;
    protected $fieldRepository;
    protected $tableRepository;
    protected $permissionsService;
    protected $service;

    /**
     * TableFieldService constructor.
     */
    public function __construct()
    {
        $this->fieldLinkRepository = new TableFieldLinkRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->tableRepository = new TableRepository();
        $this->permissionsService = new UserPermissionsService();
        $this->service = new HelperService();
    }

    /**
     * Get table fields with user settings for each field
     *
     * @param $table
     * @param $user_id - integer|null
     * @param $view_columns - array // column fields ['col_1','col_5',...]
     *
     * @return Collection of TableFields
     */
    public function getWithSettings($table, $user_id, array $view_columns = [])
    {
        $this->fieldRepository->removePresaved($table);

        if ($table->__data_permission_id || $table->__data_dcr_id) {
            $user_id = null;
        }

        if ($user_id && auth()->user() && auth()->user()->isAdmin()) {
            $user_id = $table->user_id; //Admin can access to all tables and their columns
        }

        //if user doesn`t have settings for fields
        if ($table->user_id != $user_id && $this->fieldRepository->haveFieldsWithoutSettings($table, $user_id)) {
            //copy settings from owner to user
            $this->fieldRepository->copySettingsFromOwner($table, $user_id);
        }

        $settings = $this->fieldRepository->getFieldsWithHeaders($table, $user_id);

        //hide c2m2 for /visiting/ path
        if ($this->service->isVisitingUrl()) {
            $view_columns['hidden'] = array_merge($view_columns['hidden'] ?? [], $this->service->c2m2_fields);
        }

        //disable columns which were not present in 'View'
        if (isset($view_columns['visible'])) {
            $visi = $view_columns['visible'] ?? [];
            foreach ($settings as $col) {
                if (!in_array($col->field, $visi)) {
                    //IF edited by owner just hide columns ELSE set columns as unavailable
                    (!empty($view_columns['edited_by_owner'])
                        ? $col->is_showed = 0
                        : $col->_permis_hidden = true);//equivalent of '_current_right.view_fields[i]
                }
            }
        }

        //hide columns which were hidden in 'View'
        if (!empty($view_columns['temp_hidden'])) {
            $hide = $view_columns['temp_hidden'] ?? [];
            foreach ($settings as $col) {
                if ($hide && in_array($col->field, $hide)) {
                    $col->is_showed = 0;
                }
            }
        }

        //set orders and sizes for columns saved in 'View'
        if (!empty($view_columns['orders'])) {
            $column_orders = collect($view_columns['orders']);
            foreach ($settings as $col) {
                $ord = $column_orders->where('id', $col->id)->first();
                if ($ord) {
                    $col->order = $ord['order'] ?? $col->order;
                    $col->width = $ord['width'] ?? $col->width;
                }
            }
        }

        //apply permissions if:
        //user not owner AND (table is not system OR system available for all)
        $permis = TableRights::permissions($table);
        if (
            !$permis->is_owner
            &&
            (!$table->is_system || in_array($table->db_name, $this->service->system_tables_for_all))
        ) {
            $view_fields = $permis->view_fields->toArray();
            foreach ($settings as $col) {
                if (!in_array($col->field, $view_fields)) {
                    $col->is_showed = 0;
                    $col->_permis_hidden = true;//equivalent of '_current_right.view_fields[i]
                }
            }
        }

        return $this->fieldRepository->timestampsColumnsToEnd($settings);
    }

    /**
     * @param Collection $table_fields
     * @param Table $table
     */
    public function loadFldRelations(Collection $table_fields, Table $table)
    {
        $table_fields->load([
            '_links' => function ($q) {
                $q->with('_params');
            },
            '_map_icons'
        ]);
        //Load Kanban settings for table with relative Addon
        if ($table->add_kanban) {
            $table_fields->load([
                '_kanban_setting' => function ($q) {
                    $q->with('_fields_pivot');
                },
            ]);
        }
        //Link Unit Fields with RC_id
        (new TableDataRowsRepository())->attachRCidToUnits($table_fields, $table);
    }

    /**
     * Get table fields with applying of permissions and standard settings.
     *
     * @param Collection|\Vanguard\Models\Table\Table $tables
     * @param $user_id - integer|null
     * @param $with_permissions - bool
     *
     * @return array of TableFields
     */
    public function loadFieldsWithStandardSettings($tables, $user_id, $with_permissions = true) {
        $this->fieldRepository->loadStandardSettings($tables, $user_id, $with_permissions);
    }

    /**
     * Get only table field info
     *
     * @param $field_id
     * @return mixed
     */
    public function getField($field_id) {
        return $this->fieldRepository->getField($field_id);
    }

    /**
     * Get table fields info
     *
     * @param $table_id
     * @return mixed
     */
    public function getFieldsForTable($table_id) {
        $table = $this->tableRepository->getTable($table_id);
        return $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id());
    }

    /**
     * Get only table info by field_id
     *
     * @param $field_id
     * @return mixed
     */
    public function getTableByField($field_id) {
        return $this->fieldRepository->getTableByField($field_id);
    }

    /**
     * @param $table_id
     * @param $field
     * @param $val
     * @return mixed
     */
    public function massSettingsUpdate($table_id, $field, $val) {
        return $this->fieldRepository->massSettingsUpdate($table_id, $field, $val);
    }

    /**
     * Update columns settings for user
     *
     * @param $field_id
     * @param $user_id
     * @param string $field_name
     * @param $val
     * @return array
     */
    public function updateSettingsRow($field_id, $user_id, string $field_name, $val, $recalc_ids) {
        $field = $this->fieldRepository->getField($field_id);
        $field->load('_table');

        $fillableFields = (new TableField())->getFillable();
        $fillableHeaders = (new UserHeaders())->getFillable();

        //can update only owner
        if ($field && $field->_table->user_id === $user_id) {

            //delete TableFieldLinks if Field's active_links was disabled
            if ($field_name == 'active_links' && $val == 0 && $field->active_links) {
                $field->_links()->delete();
            }

            //add TableKanbanSettings if Field's kanban_group was activated
            if ($field_name == 'kanban_group' && $val == 1 && !$field->kanban_group) {
                $this->fieldRepository->addKanban($field_id);
            }
            //delete TableKanbanSettings if Field's kanban_group was disabled
            if ($field_name == 'kanban_group' && $val == 0 && $field->kanban_group) {
                $field->_kanban_setting()->delete();
            }

            //Run background process to fill All DDLs in table if activated ddl_auto_fill
            if ($field_name == 'ddl_auto_fill' && $val == 1 && !$field->ddl_auto_fill) {
                dispatch(new AutoFillAllDdls($field->_table, $user_id));
            }

            //can update
            if (in_array($field_name, $fillableFields)) {
                //'Radio' can be checked only one
                if (in_array($field_name, TableField::$radioFields)) {
                    $this->fieldRepository->updateMassTableField( $field->table_id, array_merge([$field_name => 0], $this->service->getModified()) );
                }
                //update field settings
                $resf = $this->fieldRepository->updateTableField( $field->_table, $field_id, [$field_name => $val], $recalc_ids );

                //spec system setting
                if (in_array($field_name, ['is_gantt_main_group','is_gantt_parent_group','is_gantt_group']) && $val) {
                    $gantt_group_fld = $this->fieldRepository->getFieldsForTable($field->table_id);
                    $gantt_group_fld = $gantt_group_fld->where('is_gantt_group', '=', 1)->first();
                    $this->fieldRepository->updateMassTableField( $field->table_id, ['is_gantt_left_header' => 0] );
                    $this->fieldRepository->updateTableField( $field->_table, $gantt_group_fld->id, ['is_gantt_left_header' => 1] );
                }
            }

            //Run background process to fill All DDLs in table if activated ddl_auto_fill
            if ($field_name == 'mirror_rc_id' || $field_name == 'mirror_field_id' || $field_name == 'mirror_part') {
                dispatch(new FillMirrorValues($field->id));
            }

            //Run background process to fill All DDLs in table if activated ddl_auto_fill
            if ($field_name == 'fetch_source_id') {
                dispatch(new WatchRemoteFiles($field->table_id, $field->id));
            }

            //watch for sys columns
            if ($field_name == 'input_type') {
                $new_field = $this->fieldRepository->getField($field_id);
                (new SysColumnCreator())->watchInputType($field->_table, $new_field);
            }
        }
        else {
            //can update collaborator
            if (in_array($field_name, $fillableHeaders)) {
                //'Radio' can be checked only one
                if (in_array($field_name, TableField::$radioFields)) {
                    $this->fieldRepository->updateMassUserHeader( $field->table_id, $user_id, array_merge([$field_name => 0], $this->service->getModified()) );
                }
                //update field settings
                $resh = $this->fieldRepository->updateUserHeader( $field_id, $user_id, array_merge([$field_name => $val], $this->service->getModified()) );
            }
        }

        $collect = $this->fieldRepository->getField([$field_id], $field->_table);
        $this->loadFldRelations($collect, $field->_table);
        return $collect->first();
    }

    /**
     * @param int $table_id
     * @param array $datas
     * @return mixed
     */
    public function justUserSetts(int $table_id, array $datas)
    {
        $this->tableRepository->saveUserSettings($table_id, $datas);
        return $this->tableRepository->getUserSettings($table_id);
    }

    /**
     * @param int $table_id
     * @param array $ids
     * @return mixed
     */
    public function selFields(int $table_id, array $ids)
    {
        return $this->fieldRepository->selFields($table_id, $ids);
    }

    /**
     * @param Table $table
     * @param string $field
     * @param DDL $ddl
     * @param string $select_type
     */
    public function applyDDLtoField(Table $table, string $field, DDL $ddl, string $select_type = 'M-Select')
    {
        $fld = $this->fieldRepository->getFieldBy($table->id, 'field', $field);
        if ($fld) {
            $this->fieldRepository->updateTableField($table, $fld->id, [
                'ddl_id' => $ddl->id,
                'input_type' => $select_type,
            ]);
        }
    }
}