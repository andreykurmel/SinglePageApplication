<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\FormulaSymbolCreator;
use Vanguard\Classes\Randoms;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Models\Table\TableSRV2Fields;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaUpdaterRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Watchers\FieldRenameWatcher;

class TableFieldRepository
{
    protected $service;
    protected $presaved = 'presaved';
    protected $prevented = [
        'mselect' => [],
    ];

    /**
     * TableFieldRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get only table field info
     *
     * @param $field_ids
     * @param Table|null $table
     * @return mixed
     */
    public function getField($field_ids, Table $table = null)
    {
        $dbname = (new TableField())->getTable();
        if (is_array($field_ids)) {
            return $table
                ? TableField::whereIn($dbname.'.id', $field_ids)->joinHeader(auth()->id(), $table)->get()
                : TableField::whereIn($dbname.'.id', $field_ids)->get();
        } else {
            return $table
                ? TableField::where($dbname.'.id', '=', $field_ids)->joinHeader(auth()->id(), $table)->first()
                : TableField::where($dbname.'.id', '=', $field_ids)->first();
        }
    }

    /**
     * @param int $table_id
     * @param string $field
     * @param $search
     * @return TableField|null
     */
    public function getFieldBy(int $table_id, string $field, $search)
    {
        return TableField::where('table_id', '=', $table_id)
            ->where($field, '=', $search)
            ->first();
    }

    /**
     * Get only table info by field_id
     *
     * @param $field_id
     * @return mixed
     */
    public function getTableByField($field_id)
    {
        $field = TableField::where('id', '=', $field_id)->first();
        return $field->_table;
    }

    /**
     * @param $table_id
     * @param $field
     * @param $val
     * @param array $limit_fields
     * @return bool|int
     */
    public function massSettingsUpdate($table_id, $field, $val, array $limit_fields = []) {
        //Used for "Set Mass Status"
        $sql = TableField::where('table_id', '=', $table_id);
        if ($limit_fields) {
            $sql->whereIn('field', $limit_fields);
        }
        return $sql->update([
            $field => $val
        ]);
    }

    /**
     * Get table fields info
     *
     * @param $table_id
     * @return mixed
     */
    public function getFieldsForTable($table_id)
    {
        return TableField::where('table_id', '=', $table_id)->get();
    }

    /**
     * Get table fields with user headers
     *
     * @param Table $table
     * @param $user_id
     * @param $floating
     * @return Collection
     */
    public function getFieldsWithHeaders(Table $table, $user_id = null, $floating = false)
    {
        return TableField::where('table_id', '=', $table->id)
            ->joinHeader( $user_id, $table, $floating )
            ->get();
    }

    /**
     * Get table fields with applying of permissions and standard settings.
     *
     * @param Collection $tables
     * @param $user_id - integer|null
     * @param $with_permissions - bool
     */
    public function loadStandardSettings($tables, $user_id, $with_permissions = true)
    {
        if ($with_permissions) {
            $tables = $this->loadFieldsWithPermissions($tables, $user_id);
        } else {
            $tables->load([
                '_fields' => function ($f) {
                    $f->joinOwnerHeader();
                }
            ]);
        }

        if ($tables instanceof Table) {
            $_fields = $this->timestampsColumnsToEnd($tables->_fields);
            unset($tables->_fields);
            $tables->_fields = $_fields;
        } else {
            foreach ($tables as $tb) {
                $_fields = $this->timestampsColumnsToEnd($tb->_fields);
                unset($tb->_fields);
                $tb->_fields = $_fields;
            }
        }
    }

    /**
     * Check how many fields without settings user have
     *
     * @param Table $table
     * @param $user_id
     * @return integer
     */
    public function haveFieldsWithoutSettings(Table $table, $user_id)
    {
        return TableField::where('table_id', '=', $table->id)
            ->whereDoesntHave('_user_headers', function ($header_q) use ($user_id) {
                $header_q->where('user_id', '=', $user_id);
            })
            ->count();
    }

    /**
     * Create settings with default values for table owner
     *
     * @param Table $table
     */
    public function createSettingsForOwner(Table $table)
    {
        $table_fields = $table->_fields()->get();
        $table_fields = $table_fields->sortBy(function ($field, $key) {
            if ($field->field == 'id') {
                $res = 0;
            } elseif (!in_array($field->field, $this->service->system_fields)) {
                $res = $field->id;
            } else {
                $res = PHP_INT_MAX;
            }
            return $res;
        });
        $absent_field_ids = TableField::where('table_id', '=', $table->id)
            ->whereDoesntHave('_user_headers', function ($header_q) use ($table) {
                $header_q->where('user_id', '=', $table->user_id);
            })
            ->orderBy('id')
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        $allData = [];
        $idx = count($table_fields) - count($absent_field_ids);
        $dataSave = array_merge(['user_id' => $table->user_id], $this->service->getModified(), $this->service->getCreated());
        foreach ($table_fields as $field) {
            if (in_array($field->id, $absent_field_ids)) {
                $dataSave['table_field_id'] = $field->id;
                $dataSave['order'] = ++$idx;
                $dataSave['web'] = in_array($field->field, $this->service->c2m2_fields) ? 0 : 1;
                $allData[] = $dataSave;
            }
        }
        UserHeaders::insert($allData);
    }

    /**
     * Get table fields with applying of permissions and standard settings.
     *
     * @param Collection|Table $tables
     * @param $user_id - integer|null
     * @param $type - string: ['view' or 'edit']
     * @return Collection of Table or TableField
     */
    public function loadFieldsWithPermissions($tables, $user_id, $type = 'view')
    {
        $repo = $this;
        if ($tables instanceof Table) {
            $query = $tables->_fields();
            $this->fieldsPermissionsQuery($query, $user_id, $type, $tables);
            //TableFields
            return $query->get();
        } else {
            $tables->load([
                '_fields' => function ($query) use ($repo, $user_id, $type) {
                    $repo->fieldsPermissionsQuery($query, $user_id, $type, new Table());
                }
            ]);
            //Tables
            return $tables;
        }
    }

    /**
     * @param $query
     * @param $user_id
     * @param string $type
     * @param Table $table
     */
    protected function fieldsPermissionsQuery($query, $user_id, $type = 'view', Table $table)
    {
        $visitor_scope = $table->is_system || !$user_id || $this->service->use_visitor_scope;
        $query->joinOwnerHeader();
        $query->where(function ($inner) use ($visitor_scope, $user_id, $type, $table) {
            //user is owner
            $inner->whereHas('_table', function ($t) use ($user_id) {
                $t->where('user_id', $user_id);
            });
            //or user has permission to view field
            $inner->orWhereHas('_table_column_groups', function ($tcg) use ($visitor_scope, $type, $table) {

                $vPermisId = $this->service->viewPermissionId($table);
                $tcg->orWhereHas('_table_permission_columns', function ($tpc) use ($visitor_scope, $type, $vPermisId) {
                    //can view
                    $tpc->where($type, 1);
                    //and has permission
                    $tpc->whereHas('_table_permission', function ($tp) use ($visitor_scope, $vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId, $visitor_scope);
                    });
                });

                if ($table->__data_dcr_id) {
                    $tcg->orWhereHas('_table_dcr_columns', function ($tpc) use ($visitor_scope, $type, $vPermisId) {
                        //can view
                        $tpc->where($type, 1);
                        //and linked to DCR
                        $tpc->where('table_data_requests_id', $vPermisId);
                    });
                }

            });
        });
    }

    /**
     * Move 'timestamps columns' to the end.
     *
     * @param Collection $fields
     * @return Collection
     */
    public function timestampsColumnsToEnd(Collection $fields)
    {
        $max_order = 0;
        foreach ($fields as $set) {
            if ($set->field == 'id') {
                $set->order = 0;
            }

            $max_order = max($max_order, $set->order);

            if (in_array($set->field, $this->service->c2m2_fields)) {
                $set->order = 999;
            }
        }

        foreach ($fields as $set) {
            if (!$set->order && $set->field != 'id') {
                $set->order = $max_order++;
                TableField::where('id', $set->id)->update([
                    'order' => $set->order
                ]);
            }
        }

        return $fields->sortBy('order')->values();
    }

    /**
     * Set Standard Headers for TableFields.
     *
     * @param Table $table
     */
    public function setStandardHeaders(Table $table)
    {
        foreach ($table->_fields as $set) {
            $set->width = $this->service->def_col_width;
            $set->is_showed = 1;

            //move 'timestamps columns' to the end
            $set->order = $set->field == 'id' ? 0 : 1;
            if ($set->field != 'id' && in_array($set->field, $this->service->system_fields)) {
                $set->order = 999;
            }
        }
        $sorted = $table->_fields->sortBy('order')->values();
        $table->setRelation('_fields', $sorted);
    }

    /**
     * Add records into the 'table_fields' for just created table
     *
     * @param int $table_id
     * @param array $datas :
     * [
     *  [
     *      +field: string,
     *      +name: string,
     *      +f_type: string,
     *  ],
     *  ...
     * ]
     * @param bool $presave
     * @return array
     */
    public function addFieldsForCreatedTable(int $table_id, array $datas, bool $presave = false)
    {
        //$symCreator = new FormulaSymbolCreator();
        $new_models = [];
        foreach ($datas as $idx => $data) {
            $data = array_merge($data, $this->service->getCreated(), $this->service->getModified());
            $data['name'] = $data['name'] ?: 'col_' . $idx;
            $data['field'] = empty($data['field']) ? $this->getDbField($data['name'], $idx) : $data['field'];
            $data['table_id'] = $table_id;
            $data['ddl_auto_fill'] = $data['ddl_auto_fill'] ?? 0;
            $data['ddl_add_option'] = $data['ddl_add_option'] ?? 0;
            $data['f_default'] = $this->service->getDefaultOnType($data['f_type']);
            $data['row_hash'] = $presave ? $this->presaved : Uuid::uuid4();
            $new_models[] = TableField::updateOrCreate([
                'table_id' => $data['table_id'],
                'field' => $data['field'],
            ], $data);
        }
        return $new_models;
    }

    /**
     * @param Table $table
     * @return mixed
     */
    public function removePresaved(Table $table)
    {
        return TableField::where('table_id', '=', $table->id)
            ->where('row_hash', '=', $this->presaved)
            ->delete();
    }

    /**
     * Get db_name for field from name
     *
     * @param $name
     * @param $idx
     * @return string
     */
    public function getDbField($name, $idx)
    {
        $name = strtolower($name) ?: 'fld';
        $name = preg_replace('/[^\w\d_]/i', '', $name);
        $name = preg_replace('/ /i', '_', substr($name, 0, 50));

        if (!$name || preg_match('/[^a-zA-Z]/i', $name[0])) {
            $name = 'fld_' . $name;
        }
        if (strlen($name) < 3) {
            $name .= Str::random(5);
        }
        return $name . '_' . ($idx);// . Randoms::rand_one();
    }

    /**
     * Change records in the 'table_fields' for just modified table
     *
     * @param Table $table
     * @param array $datas :
     * [
     *  [
     *      +status: string,
     *      +field: string,
     *      +name: string,
     *      +f_type: string,
     *      +f_size: float,
     *      +f_required: int(0|1),
     *  ],
     *  ...
     * ]
     */
    public function changeFieldsForModifiedTable(Table $table, $datas)
    {
        //$symCreator = new FormulaSymbolCreator();
        $remoteFileRepo = new RemoteFilesRepository();
        $fileRepo = new FileRepository();
        $dataRepo = new FormulaUpdaterRepository();
        $fldRepo = new TableFieldRepository();
        $order = 1;//'id';
        foreach ($datas as $data) {
            $selflds = ['field', 'name', 'formula_symbol', 'f_type', 'f_size', 'f_required', 'f_format', 'rating_icon'];
            $arr = collect($data)
                ->whereNotIn('field', $this->service->system_fields)
                ->only($selflds)
                ->toArray();
            if ($data['status'] !== 'del') {
                $order++;
                $arr['order'] = $order;
            }

            $db_field = $fldRepo->getFieldBy($table->id, 'field', $arr['field']);
            if ($data['status'] == 'del' && $db_field) {
                $this->removeFieldCorrectly($table, $db_field, $data);
            }
            if ($data['status'] == 'edit' && $db_field) {
                $arr = $this->setSpecialConditions($arr, 'update');
                $fldRepo->updateTableField( $table, $db_field->id, array_merge($arr, $this->service->getModified()) );
                $dataRepo->updateNamesInFormulaes($table, $db_field, $arr);
            }
            if ($data['status'] == 'add') {
                $arr['table_id'] = $table->id;
                $arr = $this->setSpecialConditions($arr, 'add');
                $fldRepo->addFieldsForCreatedTable($table->id, [$arr]);
            }
        }

        $table->load('_fields');

        //fill empty auto strings/numbers
        foreach ($datas as $data) {
            if (in_array($data['f_type'], ['Auto String', 'Auto Number'])) {
                (new ImportRepository())->fillEmptyAutoRows($table, $data);
            }
        }

        //Create system RCs and RowGroups
        $userFields = $table->_fields
            ->where('f_type', '=', 'User')
            ->whereNotIn('field', $this->service->system_fields);
        foreach ($userFields as $userField) {
            (new TableRefConditionRepository())->checkAndMakeSysRCforUserField($table, $userField->id);
            (new TableRowGroupRepository())->checkAndMakeSysGroupsForUserField($table, $userField->id);
        }

        //Change "temp table reference" to "table id" for columns with type="TableField"
        $tableFieldCols = $table->_fields->where('f_type', '=', 'TableField');
        foreach ($tableFieldCols as $col) {
            if ($col->f_format && !is_numeric($col->f_format)) {
                $refTb = $table->_fields->where('name', '=', $col->f_format)->first();
                $col->update([
                    'f_format' => $refTb ? $refTb->id : '',
                ]);
            }
        }
    }

    /**
     * @param Table $table
     * @param TableField $field
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function removeFieldCorrectly(Table $table, TableField $field, $data): void
    {
        $remoteFileRepo = new RemoteFilesRepository();
        $fileRepo = new FileRepository();

        if ($data['f_type'] == 'Attachment') {
            $fileRepo->deleteAttachmentsOfColumn($table, $data['field']);
            $remoteFileRepo->delete($table->id, $field->id);
        }
        TableField::where('table_id', '=', $table->id)->where('field', '=', $data['field'])->delete();
        //CorrespField::where('user_id', '=', $table->user_id)->where('data_field', '=', $data['field'])->delete();
    }

    /**
     * @param array $arr
     * @param string $type
     * @return array
     */
    protected function setSpecialConditions(array $arr, string $type)
    {
        if ($type == 'update') {
            if (in_array($arr['f_type'], ['Attachment'])) {
                $arr['is_search_autocomplete_display'] = 0;
            }
        }
        if ($type == 'add') {
            $arr['ddl_auto_fill'] = 0;
            $arr['ddl_add_option'] = 0;
            $arr['show_zeros'] = 0;
            $arr['f_default'] = $this->service->getDefaultOnType($arr['f_type']);
        }

        if (in_array($arr['f_type'] ?? '', ['User','Color'])) {
            $arr['input_type'] = 'S-Select';
        }
        return $arr;
    }

    /**
     * Copy settings for user from owner`s settings
     *
     * @param Table $table
     * @param $user_id
     */
    public function copySettingsFromOwner(Table $table, $user_id)
    {
        if (is_null($user_id)) {
            return;
        }
        $hdr_fillable = (new UserHeaders())->getFillable();

        $table_fields = TableField::where('table_id', '=', $table->id)->get();

        $absent_field_ids = TableField::where('table_id', '=', $table->id)
            ->whereDoesntHave('_user_headers', function ($header_q) use ($user_id) {
                $header_q->where('user_id', '=', $user_id);
            })
            ->get()
            ->pluck('id')
            ->toArray();

        $allData = [];
        foreach ($table_fields as $field) {
            if (in_array($field->id, $absent_field_ids)) {
                $user_header = $field->only( $hdr_fillable );
                $dataSave = array_merge(
                    $user_header,
                    ['user_id' => $user_id, 'table_field_id' => $field->id],
                    $this->service->getModified(),
                    $this->service->getCreated()
                );
                if ($dataSave['id'] ?? '') {
                    unset($dataSave['id']);
                }
                $allData[] = $dataSave;
            }
        }
        UserHeaders::insert($allData);
    }

    /**
     * Update table field
     *
     * @param Table $table
     * @param $row_id
     * @param array $params
     * @return mixed
     */
    public function updateTableField(Table $table, $row_id, array $params)
    {
        $fld = TableField::where('id', '=', $row_id)->first()->toArray();
        $changed_fld = Arr::first(array_keys($params));

        $old_f_formula = ($fld['f_formula'] ?? '');
        $combined = array_merge($fld, $params);
        $new_f_formula = ($combined['f_formula'] ?? '');

        $combinedInputType = $combined['input_type'] === 'Mirror' && $combined['mirror_rc_id']
            ? $combined['mirror_edit_component']
            : $combined['input_type'];

        if ($changed_fld == 'unit_ddl_id') {
            $combined['unit_display'] = null;
            $combined['unit'] = null;
        }
        if ($changed_fld == 'fetch_by_row_cloud_id') {
            $combined['fetch_one_cloud_id'] = null;
        }
        if ($changed_fld == 'fetch_one_cloud_id') {
            $combined['fetch_by_row_cloud_id'] = null;
        }

        //store prevs
        if ($changed_fld == 'input_type') {
            $combined['prev_input_type'] = $fld['input_type'];
        }
        if ($changed_fld == 'ddl_id') {
            $combined['prev_ddl_id'] = $fld['ddl_id'];
            $ddl = (new DDLRepository())->getDDL($combined['ddl_id'] ?? 0);
            if ($ddl && $ddl->table_id != $table->id) {
                $combined['shared_ddlref_ids'] = $ddl->_references
                    ->pluck('id')
                    ->map(function ($item) {
                        return (string)$item;
                    })
                    ->toJson();
            } else {
                $combined['shared_ddlref_ids'] = null;
            }
        }

        //3rd party Twilio
        if ($changed_fld == 'twilio_google_acc_id') {
            $combined['twilio_sendgrid_acc_id'] = null;
        }
        if ($changed_fld == 'twilio_sendgrid_acc_id') {
            $combined['twilio_google_acc_id'] = null;
        }

        if (!in_array($combinedInputType, $this->service->select_input)) {
            $combined['ddl_id'] = null;
        }
        if ($combinedInputType == 'Input' && $combined['ddl_auto_fill']) {
            $combined['ddl_auto_fill'] = 0;
        }
        if ($combinedInputType == 'Input' && $combined['ddl_add_option']) {
            $combined['ddl_add_option'] = 0;
        }
        if ($combinedInputType != 'Formula' && $combined['is_uniform_formula']) {
            $combined['is_uniform_formula'] = 0;
        }
        if ($fld['unit'] != $combined['unit']) {
            $combined['unit_display'] = $combined['unit'];
        }
        if (!$combined['filter'] && $combined['filter_search']) {
            $combined['filter_search'] = 0;
        }

        if ($fld['name'] != $combined['name']) {
            (new FieldRenameWatcher())->watchRename($table, $fld['name'], $combined['name']);
        }
        if ($fld['formula_symbol'] != $combined['formula_symbol']) {
            (new FieldRenameWatcher())->watchRename($table, $fld['formula_symbol'] ?: '', $combined['formula_symbol'] ?: $combined['name']);
        }

        $combined['row_hash'] = Uuid::uuid4();

        $uniform_active_and_changed_formula = $combined['is_uniform_formula'] && $old_f_formula != $new_f_formula;
        $uniform_is_activated = !$fld['is_uniform_formula'] && $combined['is_uniform_formula'];
        if ($uniform_active_and_changed_formula || $uniform_is_activated) {
            (new TableDataService())->setDefFormulaToAll($table, $fld['field'], $new_f_formula);
        }

        $updated = TableField::where('id', '=', $row_id)
            ->update( array_merge($this->service->delSystemFields($combined), $this->service->getModified()) );

        return $updated;
    }

    /**
     * Update table field
     *
     * @param $table_id
     * @param array $params
     * @return mixed
     */
    public function updateMassTableField($table_id, Array $params)
    {
        //For Radio Fields
        return TableField::where('table_id', '=', $table_id)
            ->update($this->service->delSystemFields($params));
    }

    /**
     * Update user setting row
     *
     * @param $row_id
     * @param $user_id
     * @param array $params
     * @return mixed
     */
    public function updateUserHeader($row_id, $user_id, Array $params)
    {
        return UserHeaders::where('table_field_id', '=', $row_id)
            ->where('user_id', '=', $user_id)
            ->update($this->service->delSystemFields($params));
    }

    /**
     * Update user setting row
     *
     * @param $table_id
     * @param $user_id
     * @param array $params
     * @return mixed
     */
    public function updateMassUserHeader($table_id, $user_id, Array $params)
    {
        $ids = TableField::where('table_id', '=', $table_id)
            ->get()
            ->pluck('id');
        return UserHeaders::whereIn('table_field_id', $ids)
            ->where('user_id', '=', $user_id)
            ->update($this->service->delSystemFields($params));
    }

    /**
     * @param int $table_id
     * @param array $ids
     * @return mixed
     */
    public function selFields(int $table_id, array $ids)
    {
        return TableField::where('table_id', '=', $table_id)
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @param int $field_id
     */
    public function addKanban(int $field_id)
    {
        if (!TableKanbanSettings::where('table_field_id', '=', $field_id)->count()) {
            TableKanbanSettings::create([
                'table_field_id' => $field_id,
            ]);
        }
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @return void
     */
    public function srvAttachIfNeeded(int $table_id, int $field_id)
    {
        TableSRV2Fields::updateOrCreate([
            'table_id' => $table_id,
            'table_field_id' => $field_id,
        ], [
            'table_id' => $table_id,
            'table_field_id' => $field_id,
        ]);
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @param string $setting
     * @param $val
     * @return void
     */
    public function srvChangePivotFld(int $table_id, int $field_id, string $setting, $val)
    {
        TableSRV2Fields::where('table_id', '=', $table_id)
            ->where('table_field_id', '=', $field_id)
            ->update([
                $setting => $val
            ]);
    }
}