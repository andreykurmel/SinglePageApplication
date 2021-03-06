<?php


namespace Vanguard\Services\Tablda;


use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;
use Vanguard\Jobs\BatchUploadingJob;
use Vanguard\Jobs\FormulaWatcherJob;
use Vanguard\Jobs\RecalcTableFormula;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Support\FileHelper;
use Vanguard\User;

class TableDataService
{
    protected $fileRepository;
    protected $tableDataRepository;
    protected $dataRowsRepository;
    protected $tableRepository;
    protected $fieldRepository;
    protected $refConditionRepository;
    protected $rowGroupRepository;
    protected $DDLRepository;
    protected $tableAlertService;
    protected $service;

    /**
     * TableDataService constructor.
     */
    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->tableDataRepository = new TableDataRepository();
        $this->dataRowsRepository = new TableDataRowsRepository();
        $this->tableRepository = new TableRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->refConditionRepository = new TableRefConditionRepository();
        $this->rowGroupRepository = new TableRowGroupRepository();
        $this->DDLRepository = new DDLRepository();
        $this->tableAlertService = new TableAlertService();
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     * @param int $row_group_id
     * @return int
     */
    public function countRowGroupRows(Table $table, int $row_group_id)
    {
        $sql = new TableDataQuery($table, true, auth()->id() ?: 0);
        $sql->applySelectedRowGroup($row_group_id);
        return $sql->getQuery()->count();
    }

    /**
     * @param Table $table
     * @param array $parameters
     * @return int
     */
    public function removeDuplicates(Table $table, array $parameters): int
    {
        return (new DuplicateDataService($parameters))->removeDuplicates($table);
    }

    /**
     * @param Table $table
     * @param int $url_field_id
     * @param int $attach_field_id
     * @param int|null $row_group_id
     * @return int
     */
    public function uploadingJob(Table $table, int $url_field_id, int $attach_field_id, int $row_group_id = null): int
    {
        $job = Import::create([
            'file' => '',
            'status' => 'initialized'
        ]);

        dispatch(new BatchUploadingJob($job->id, $table, $url_field_id, $attach_field_id, $row_group_id));

        return $job->id;
    }

    /**
     * @param int $job_id
     * @param Table $table
     * @param int $url_field_id
     * @param int $attach_field_id
     * @param int|null $row_group_id
     * @return int
     */
    public function batchUploading(int $job_id, Table $table, int $url_field_id, int $attach_field_id, int $row_group_id = null): int
    {
        $sql = new TableDataQuery($table);
        if ($row_group_id) {
            $sql->applySelectedRowGroup($row_group_id);
        }
        $sql = $sql->getQuery();

        $url = $table->_fields->where('id', '=', $url_field_id)->first();
        $filerepo = new FileRepository();

        $page = 10;
        $lines = $sql->count();
        for ($cur = 0; ($cur * $page) < $lines; $cur++) {
            DB::connection('mysql')->table('imports')->where('id', '=', $job_id)->update([
                'complete' => (int)((($cur * $page) / $lines) * 100)
            ]);

            $rows = $sql->offset($cur * $page)
                ->limit($page)
                ->get();

            foreach ($rows as $row) {
                try {
                    $filelink = $row[$url->field];
                    $filename = FileHelper::name($filelink);
                    $filecontent = file_get_contents($filelink);
                    $filerepo->insertFileAlias($table->id, $attach_field_id, $row['id'], $filename, $filecontent);
                } catch (\Exception $e) {
                    Log::info('TableDataService - batchUploading Error');
                    Log::info($e->getMessage());
                }
            }
        }

        DB::connection('mysql')->table('imports')->where('id', '=', $job_id)->update([
            'status' => 'done'
        ]);

        return $lines;
    }

    /**
     * Search Rows for Replace.
     *
     * @param Table $table
     * @param string $search
     * @param array $columns
     * @param array $request_params
     * @return array
     */
    public function findReplace(Table $table, string $search, array $columns, array $request_params)
    {
        $user_fields = $this->tableDataRepository->tableUserFields($table);
        $sql = new TableDataQuery($table);

        $search_group = $user_fields ? $sql->changeFindStrForUserColumns($user_fields[0], $search) : '';
        $search_arr = $search_group ? [$search, $search_group] : [$search];

        $sql = $this->prepareReplaceSql($sql, $search_arr, $columns, $request_params);

        $sql_total = (new TableDataQuery($table));
        $sql_total = $this->prepareReplaceSql($sql_total, $search_arr, $columns, $request_params, false);

        return [
            $sql_total->getQuery()->count(),
            $sql->getQuery()->count(),
        ];
    }

    /**
     * Apply Search terms for replace data in rows.
     *
     * @param TableDataQuery $sql
     * @param array $search_arr
     * @param array $columns
     * @param array $request_params
     * @param bool $row_group_edit_apply
     * @param bool $force
     * @return TableDataQuery
     */
    protected function prepareReplaceSql(TableDataQuery $sql, array $search_arr, array $columns, array $request_params, bool $row_group_edit_apply = true, bool $force = false)
    {
        $sql->applyWhereClause($request_params, auth()->id(), [
            'row_group_edit' => true,
            'row_group_edit_apply' => $row_group_edit_apply,
            'force_execute' => $force,
        ]);

        $search_words = [];
        foreach ($search_arr as $elem) {
            if (trim($elem)) {
                $search_words[] = [
                    'word' => trim($elem),
                    'type' => 'or'
                ];
            }
        }
        $replace_search = [
            'search_words' => $search_words,
            'search_columns' => $columns
        ];

        $sql->applySearch($replace_search, true);
        return $sql;
    }

    /**
     * Get provided params as applied filters for table.
     *
     * @param $table_id
     * @param $fields
     * @return array
     */
    public function getAsAppliedFilters($table_id, $fields)
    {
        $applied_filters = [];
        $idx = 1;
        foreach ($fields as $key => $val) {
            $applied_filters[] = [
                'applied_index' => $idx++,
                'field' => $key,
                'values' => [
                    [
                        'checked' => 1,
                        'val' => $val,
                        'show' => $val,
                        'rowgroup_disabled' => false,
                    ]
                ]
            ];
        }

        return ($applied_filters ? $this->tableDataRepository->getFilters($table_id, ['applied_filters' => $applied_filters]) : []);
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @return array
     */
    public function getAllValuesForField(int $table_id, int $field_id): array
    {
        $table = $this->tableRepository->getTable($table_id);
        $fld = $this->fieldRepository->getField($field_id);
        $datas = [];
        foreach ($this->distinctiveFieldValues($table, $fld) as $val => $show) {
            $datas[] = [
                'show' => $show,
                'val' => $val,
            ];
        }
        return array_values($datas);
    }

    /**
     * Get distinct values for one field.
     *
     * @param Table $table
     * @param TableField $field
     * @param int $limit
     * @return array
     */
    public function distinctiveFieldValues(Table $table, TableField $field, int $limit = 1000): array
    {
        return $this->tableDataRepository->getDistinctiveField($table, $field, $limit);
    }

    /**
     * Get Linked Rows for Cell
     *
     * @param Table $table
     * @param array $link
     * @param array $table_row
     * @param int $page
     * @param int $spec_limit
     * @param int|null $uid
     * @return array
     */
    public function getFieldRows(Table $table, array $link, array $table_row, int $page = 0, int $spec_limit = 0, int $uid = null)
    {
        $table_row = $this->changeLinkRowForSysTable($table, $link, $table_row);

        $ref_cond = $this->refConditionRepository->getRefCondWithRelations($link['table_ref_condition_id']);

        $uid = $uid ?: auth()->id() ?: true;
        $sql = new TableDataQuery($table);
        $sql->applyRefConditionRow($ref_cond, $table_row, !$link['always_available'], $uid);

        $row_count = $sql->getQuery()->count();

        if ($page > 0) {
            $max_limit = $spec_limit ?: $table->max_rows_in_link_popup ?: 200;
            $rows = $sql->getQuery()
                ->offset(($page - 1) * $max_limit)
                ->limit($max_limit)
                ->get();
            $this->dataRowsRepository->attachSpecialFields($rows, $table, auth()->id());
        } else {
            $rows = $sql->getQuery()->get();
        }

        return [$row_count, $rows->toArray()];
    }

    /**
     * @param Table $table
     * @param array $link
     * @param array $table_row
     * @return array
     */
    protected function changeLinkRowForSysTable(Table $table, array $link, array $table_row)
    {
        if ($table->is_system == 2) {
            $link_table = $this->fieldRepository->getTableByField($link['table_field_id']);

            if (
                $table->db_name == 'correspondence_fields'
                && $link_table->db_name == 'correspondence_stim_3d'
                && !empty($table_row['db_table'])
            ) {
                $stim_table = (new StimSettingsRepository())->getTableBy('app_table', $table_row['db_table']);
                $table_row['db_table'] = $stim_table ? $stim_table->id : $table_row['db_table'];
            }
        }
        return $table_row;
    }

    /**
     * @param Table $table
     * @param array $linked_params
     * @return array
     */
    public function changeLinkedForSysTable(Table $table, array $linked_params)
    {
        if ($table->is_system == 2) {
            if (!empty($linked_params['correspondence_table_id'])) {
                $stim_table = (new StimSettingsRepository())->getTableBy('app_table', $linked_params['correspondence_table_id']);
                $linked_params['correspondence_table_id'] = $stim_table ? $stim_table->id : $linked_params['correspondence_table_id'];
            }
        }
        return $linked_params;
    }

    /**
     * Get Rows Hashes.
     *
     * @param Table $table
     * @param array $row_ids
     * @return EloquentCollection|static[]
     */
    public function getRowsHashes(Table $table, array $row_ids)
    {
        return $this->tableDataRepository->getRowsHashes($table, $row_ids);
    }

    /**
     * Get data from user`s table
     *
     * @param array $data @inherit from TableDataRepository
     * @param integer|null - $user_id
     * @param array : $special_params [
     *      - table_permission_id: int,
     *      - view_hash: string,
     *      ^ view_object: auto-calculated TableView from 'view_hash'
     * ]
     *
     * @return array:
     * [
     *  rows: array,
     *  rows_count: int,
     *  filters: array,
     *  row_group_statuses: array, //rowGroup checked/undeterm/unchecked
     *  hidden_row_groups: int[],
     *  global_rows_count: int,
     *  version_hash: string,
     * ]
     */
    public function getRows(array $data, $user_id)
    {
        return $this->tableDataRepository->getRows($data, $user_id);
    }

    /**
     * @param Table $table
     * @param array $params
     * @return bool|mixed
     */
    public function removeByParams(Table $table, array $params)
    {
        return $this->tableDataRepository->removeByParams($table, $params);
    }

    /**
     * Insert row into the table
     *
     * @param Table $table_info
     * @param array $fields
     * @param int|null $user_id
     * @return int
     */
    public function insertRow(Table $table_info, array $fields, int $user_id = null)
    {
        $res = $this->tableDataRepository->insertRow($table_info, $fields, $user_id);

        $row = $this->getDirectRow($table_info, $res);
        $row = $row ? $row->toArray() : [];
        $special = ['user_id' => $user_id, 'permission_id' => null];
        $this->tableAlertService->checkAndSendNotifArray($table_info, 'added', [$row], [], $special);

        $this->newTableVersion($table_info);

        return $res;
    }

    /**
     * @param Table $table
     * @param $row_id
     * @param array $only
     * @return TableData
     */
    public function getDirectRow(Table $table, $row_id, array $only = [])
    {
        $row = $this->tableDataRepository->getDirectRow($table, intval($row_id));
        if ($row) {
            $collect = (new TableDataRowsRepository())->attachSpecialFields(collect([$row]), $table, auth()->id(), $only);
            return $collect->first();
        } else {
            return new TableData();
        }
    }

    /**
     * Table was changed -> set new TableVersion.
     * @param Table $table
     */
    public function newTableVersion(Table $table)
    {
        $table->version_hash = Uuid::uuid4();
        $this->tableRepository->onlyUpdateTable($table->id, $table->getAttributes());

        dispatch(new FormulaWatcherJob($table->id));
    }

    /**
     * @param Table $table_info
     * @param array $fields
     * @return int
     */
    public function insertFromAlert(Table $table_info, array $fields)
    {
        $res = $this->tableDataRepository->insertRow($table_info, $fields, $table_info->user_id);
        $row = $this->getDirectRow($table_info, $res);
        if ($row) {
            $row = $row ? $row->toArray() : [];
            $special = ['user_id' => $table_info->user_id, 'permission_id' => null];
            $this->tableAlertService->checkAndSendNotifArray($table_info, 'added', [$row], [], $special);
        }
        $this->newTableVersion($table_info);
        return $res;
    }

    /**
     * @param int $table_id
     * @param int $row_id
     */
    public function rowInTableChanged(int $table_id, int $row_id)
    {
        $table = $this->tableRepository->getTable($table_id);
        $this->tableDataRepository->quickUpdate($table, $row_id);
        $this->newTableVersion($table);
    }

    /**
     * Insert Mass Rows into Table.
     *
     * @param Table $table
     * @param array $rows
     */
    public function insertMass(Table $table, array $rows)
    {
        $this->tableDataRepository->insertMass($table, $rows);

        $this->newTableVersion($table);

        $special = ['user_id' => auth()->id()];
        $this->tableAlertService->checkAndSendNotifArray($table, 'added', $rows, [], $special);
    }

    /**
     * Update row by id
     *
     * @param Table $table_info
     * @param int $row_id
     * @param array $fields :
     *  [
     *      table_field: value,
     *      ...
     *  ]
     * @param int $user_id :
     * @param array $extra :
     *
     * @return bool|null
     * @throws Exception
     */
    public function updateRow(Table $table_info, $row_id, array $fields, $user_id, array $extra = [])
    {
        $old_row_arr = ($this->tableDataRepository->getDirectRow($table_info, $row_id));
        $old_row_arr = $old_row_arr ? $old_row_arr->toArray() : [];
        $fields_changed = $this->getChangedFields($old_row_arr, $fields);
        $updated_fields = array_merge($old_row_arr, $fields);

        $res = $this->tableDataRepository->updateRow($table_info, $row_id, $updated_fields, $user_id, $extra);

        $updated_row = $this->getDirectRow($table_info, $row_id);
        if ($res && $updated_row) {
            //Watch for changing row to sync with TableRowGroupRegular
            $this->rowGroupRepository->updateRowJsonInGroup($table_info, $row_id, json_encode($fields));

            $updated_row = $updated_row->toArray();

            //Autocomplete Row by DDLs with one value.
            $table_fields = $this->loadTableFieldForAutocomplete($table_info, $user_id);
            $updated_row_arr = $this->autocompleteRowsByTableDdls($table_fields, $table_info, [$updated_row], $user_id, 0);

            //Table hash should be changed
            $this->newTableVersion($table_info);

            //Watch for needed actions if Table is system
            $this->sysTableWatcher($table_info, $row_id, $fields);

            //Send 'updated' notifications if needed
            $special = ['user_id' => $user_id, 'permission_id' => null];
            $this->tableAlertService->checkAndSendNotifArray($table_info, 'updated', $updated_row_arr, $fields_changed, $special);
        }

        return $res;
    }

    /**
     * Get Changed fields.
     *
     * @param $new_row
     * @param $old_row
     * @return array
     */
    protected function getChangedFields($new_row, $old_row)
    {
        $changed = [];
        foreach ($new_row as $key => $val) {
            if (($old_row[$key] ?? null) != $val) {
                $changed[] = $key;
            }
        }
        return $changed;
    }

    /**
     * @param Table $table
     * @param array $updated_row
     */
    /*protected function checkUniformFormula(Table $table, array $updated_row)
    {
        foreach ($table->_fields as $header) {
            if ($header->input_type === 'Formula' && $header->is_uniform_formula) {
                $row_formla = ($updated_row[$header->field . '_formula'] ?? '');
                if ($header->f_formula && $row_formla && $header->f_formula != $row_formla) {
                    $this->tableDataRepository->setDefFormulaToAll($table, $header->field, $row_formla);
                    $header->f_formula = $row_formla;
                    $header->save();
                }
            }
        }
    }*/

    /**
     * Load Table Field For Autocomplete.
     *
     * @param Table $table_info
     * @param int|null $user_id
     * @return mixed
     */
    public function loadTableFieldForAutocomplete(Table $table_info, int $user_id = null)
    {
        $table_fields = $this->fieldRepository->getFieldsWithHeaders($table_info, $user_id);
        $ddl_relations = $this->DDLRepository->ddlRelationsForGetValues();
        $table_fields->load([
            '_ddl' => function ($ddl) use ($ddl_relations) {
                $ddl->with($ddl_relations);
            }
        ]);
        return $table_fields;
    }

    /**
     * Autocomplete Rows by DDLs and save in DB.
     *
     * @param EloquentCollection $table_fields
     * @param Table $table_info
     * @param array $updated_rows - array of Rows arrays
     * @param int|null $user_id
     * @param int|null $return_idx
     * @return array - all Rows or selected Row by $return_idx
     */
    public function autocompleteRowsByTableDdls(EloquentCollection $table_fields, Table $table_info, array $updated_rows, int $user_id = null, int $return_idx = null)
    {
        $completed_rows = [];

        foreach ($updated_rows as $r_idx => $updated_row) {
            $autocomplete = $this->doAutocompleteRow($table_fields, $updated_row, $user_id);

            if ($autocomplete['changed']) {
                $arow = $this->service->delSystemFields($autocomplete['row'], true);
                $this->tableDataRepository->updateRow($table_info, $updated_row['id'], $arow, $user_id);
            }

            if (is_null($return_idx) || $r_idx == $return_idx) {
                $completed_rows[] = $autocomplete['row'];
            }
        }

        return $completed_rows;
    }

    /**
     * Do Autocomplete Row.
     *
     * @param $table_fields
     * @param array $updated_row
     * @param $user_id
     * @return array
     */
    protected function doAutocompleteRow($table_fields, array $updated_row, $user_id)
    {
        $changed = false;
        foreach ($table_fields as $field) {
            if ($field->_ddl && $field->ddl_auto_fill && !in_array($field->f_type, ['User']) && !HelperService::isMSEL($field->input_type)) {
                $ddl_values = $this->tableDataRepository->getDDLvalues($field->_ddl, $updated_row, '', 200);
                $ddl_values = collect($ddl_values);
                if ($ddl_values->count() == 0) { //no items
                    $updated_row[$field->field] = '';
                    $changed = true;
                } elseif ($ddl_values->count() == 1) { //only one item
                    $updated_row[$field->field] = $ddl_values[0]['value'];
                    $changed = true;
                } elseif ($ddl_values->count() < 200
                    && !$ddl_values->where('value', '=', $updated_row[$field->field])->count()
                ) { //selected item is not found
                    $updated_row[$field->field] = '';
                    $changed = true;
                }
            }
        }

        return [
            'changed' => $changed,
            'row' => $updated_row
        ];
    }

    /**
     * Needed actions when updated sys Table.
     *
     * @param Table $table_info
     * @param $row_id
     * @param array $fields
     */
    protected function sysTableWatcher(Table $table_info, $row_id, array $fields)
    {
        if ($table_info->is_system) {
            //Watch for relating of APPs to selected User.
            if (
                $table_info->user_id == auth()->id() //owner
                && !empty($fields['user_id']) //and user present
                && $table_info->db_name == 'correspondence_apps'
            ) {
                $user = User::where('id', $fields['user_id'] ?? null)->first();
                CorrespApp::where('user_id', $user ? $user->id : null)
                    ->update([
                        'subdomain' => $user ? $user->subdomain : null,
                        'icon_full_path' => $user ? $user->sub_icon : null,
                    ]);
                CorrespTable::where('correspondence_app_id', $row_id)
                    ->update([
                        'user_id' => $fields['user_id'] ?? null
                    ]);
                CorrespField::where('correspondence_app_id', $row_id)
                    ->update([
                        'user_id' => $fields['user_id'] ?? null
                    ]);
            }
        }
    }

    /**
     * @param Table $table_info
     * @param $row_id
     * @param array $fields
     * @return bool|null
     */
    public function updateFromAlert(Table $table_info, $row_id, array $fields)
    {
        $old_row_arr = $this->tableDataRepository->getDirectRow($table_info, $row_id);
        $old_row_arr = $old_row_arr ? $old_row_arr->toArray() : [];
        $fields_changed = $this->getChangedFields($old_row_arr, $fields);
        $updated_fields = array_merge($old_row_arr, $fields);

        $res = $this->tableDataRepository->updateRow($table_info, $row_id, $updated_fields, $table_info->user_id);

        $updated_row = $this->getDirectRow($table_info, $row_id);
        if ($res && $updated_row) {
            $special = ['user_id' => $table_info->user_id, 'permission_id' => null];
            $this->tableAlertService->checkAndSendNotifArray($table_info, 'updated', [$updated_row->toArray()], $fields_changed, $special);
        }

        $this->newTableVersion($table_info);
        return $res;
    }

    /**
     * Autocomplete DDLs in Row and fill linked params if needed.
     *
     * @param Table $table_info
     * @param array $updated_row
     * @param int|null $user_id
     * @return array
     */
    public function checkAutocompleteNewRow(Table $table_info, array $updated_row, int $user_id = null)
    {
        $table_fields = $this->loadTableFieldForAutocomplete($table_info, $user_id);
        $autocomplete = $this->doAutocompleteRow($table_fields, $updated_row, $user_id);

        return $autocomplete['row'];
    }

    /**
     * save new Row in DB.
     *
     * @param Table $table_info
     * @param array $updated_row
     * @return array
     */
    public function saveInDbNewRow(Table $table_info, array $updated_row)
    {
        return $this->tableDataRepository->saveInDbNewRow($table_info, $updated_row);
    }

    /**
     * Delete row by id
     *
     * @param Table $table_info
     * @param int $row_id
     * @param int|null $permission
     * @return array
     */
    public function deleteRow(Table $table_info, int $row_id, int $permission = null)
    {
        $row = $this->getDirectRow($table_info, $row_id);

        $can_del = !$row || $this->tableRepository->canDelRows($table_info, $row->_applied_row_groups ?? []);
        if ($can_del !== true && !$can_del->count()) {
            return [
                'error' => 'Row is not found or permission is not available!'
            ];
        }

        $row = $row ? $row->toArray() : [];
        $special = ['user_id' => auth()->id(), 'permission_id' => $permission];
        $this->tableAlertService->checkAndSendNotifArray($table_info, 'deleted', [$row], [], $special);

        $res = $this->tableDataRepository->deleteRow($table_info, $row_id);
        $this->fileRepository->deleteFilesForRow($table_info, [$row_id]);

        $this->newTableVersion($table_info);

        return [
            'version_hash' => $table_info->version_hash,
            'row_id' => $row_id
        ];
    }

    /**
     * @param Table $table
     * @param DcrLinkedTable $dcrLinkedTable
     * @param int $parent_row_id
     * @return Collection
     */
    public function getDcrRows(Table $table, DcrLinkedTable $dcrLinkedTable, int $parent_row_id): Collection
    {
        return $this->dataRowsRepository->getDcrLinkedRows($table, $dcrLinkedTable, $parent_row_id);
    }

    /**
     * @param Table $table
     * @param string $static_hash
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getRowSRV(Table $table, string $static_hash)
    {
        $row = $this->dataRowsRepository->getRowSRV($table, $static_hash);
        return $row ? $this->getDirectRow($table, $row['id']) : null;
    }

    /**
     * Delete all rows in table
     *
     * @param Table $table_info
     * @param array $data : -> similar to getRows() method ->
     * @param int $user_id :
     * @param $ignore_hash :
     *
     * @return array
     */
    public function deleteAllRows(Table $table_info, array $data, $user_id, $ignore_hash = '')
    {
        $all_rows_sql = $this->tableDataRepository->getAllRowsSql($table_info, $data, $user_id);

        $lines = $all_rows_sql->count();
        for ($cur = 0; ($cur * 5000) < $lines; $cur++) {
            $all_rows = $all_rows_sql->offset($cur * 100)
                ->limit(100)
                ->get();

            $all_rows = $this->dataRowsRepository->attachSpecialFields($all_rows, $table_info, auth()->id(), ['groups']);

            $avail_ids = $all_rows->pluck('_applied_row_groups')->flatten()->toArray() ?? [];
            $can_del_ids = $this->tableRepository->canDelRows($table_info, $avail_ids);
            if ($can_del_ids !== true) {
                $all_rows = $all_rows->filter(function ($r) use ($can_del_ids) {
                    return $can_del_ids->intersect($r->_applied_row_groups ?? [])->count();
                });
                $data['row_id'] = $all_rows->pluck('id')->toArray();
            }

            $special = ['user_id' => $user_id];
            $this->tableAlertService->checkAndSendNotifArray($table_info, 'deleted', $all_rows->toArray(), [], $special);

            $all_ids = $this->tableDataRepository->deleteAllRows($table_info, $data, $user_id);
            $this->fileRepository->deleteFilesForRow($table_info, $all_ids);
        }

        if (!$ignore_hash) {
            $table_info->num_rows = 0;
            $this->newTableVersion($table_info);
        }

        return ['version_hash' => $table_info->version_hash];
    }

    /**
     * Delete rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @return array
     */
    public function deleteSelectedRows(Table $table, array $rows_ids, bool $force = false)
    {
        if (!$rows_ids) {
            return ['version_hash' => $table->version_hash];
        }
        $all_rows = ($this->tableDataRepository->getDirectMassRows($table, $rows_ids));
        $all_rows = $this->dataRowsRepository->attachSpecialFields($all_rows, $table, auth()->id(), ['groups']);

        if (!$force) {
            $avail_ids = $all_rows->pluck('_applied_row_groups')->flatten()->toArray() ?? [];
            $can_del_ids = $this->tableRepository->canDelRows($table, $avail_ids);
            if ($can_del_ids !== true) {
                $all_rows = $all_rows->filter(function ($r) use ($can_del_ids) {
                    return $can_del_ids->intersect($r->_applied_row_groups ?? [])->count();
                });
                $rows_ids = $all_rows->pluck('id')->toArray();
            }
        }

        $special = ['user_id' => auth()->id()];
        $this->tableAlertService->checkAndSendNotifArray($table, 'deleted', $all_rows->toArray(), [], $special);
        $res = $this->tableDataRepository->deleteSelectedRows($table, $rows_ids);
        $this->fileRepository->deleteFilesForRow($table, $rows_ids);

        $sql = new TableDataQuery($table, true);
        $table->num_rows = $sql->getQuery()->count();
        $this->newTableVersion($table);

        return ['version_hash' => $table->version_hash];
    }

    /**
     * Mass Copy rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @param array $request_params
     * @param array $replaces
     * @param array $only_columns
     * @return array
     */
    public function massCopy(Table $table, array $rows_ids = [], array $request_params = [], array $replaces = [], array $only_columns = [])
    {
        if (!count($rows_ids)) {
            $rows_ids = (new TableDataQuery($table))->getRowsIds($request_params, auth()->id());
        }

        $rows_correspondence = $this->tableDataRepository->massCopy($table, $rows_ids, $only_columns);
        $new_rows_ids = array_values($rows_correspondence);

        //copy attachments
        if ($table->_fields_are_attachments()->count()) {
            (new FileRepository())->copyAttachForRows($table->id, $rows_correspondence);
        }

        $table->num_rows += count($rows_ids);
        $this->newTableVersion($table);

        foreach ($replaces as $rep) {
            if (!empty($rep['field'])) {
                $request_params['row_id'] = $new_rows_ids;
                $request_params['search_words'] = null;
                $this->doReplace($table, $rep, [$rep['field']], $request_params, true);
            }
        }

        $rows = ($this->tableDataRepository->getDirectMassRows($table, $new_rows_ids))->toArray();
        $special = ['user_id' => auth()->id()];
        $this->tableAlertService->checkAndSendNotifArray($table, 'added', $rows, [], $special);

        return [$new_rows_ids, $rows_correspondence];
    }

    /**
     * Do Replace on searched rows.
     *
     * @param Table $table
     * @param array $replacer
     * @param array $columns
     * @param array $request_params
     * @param bool $norowhash
     * @return int
     */
    public function doReplace(Table $table, array $replacer, array $columns, array $request_params, bool $norowhash = false)
    {
        $user_fields = $this->tableDataRepository->tableUserFields($table);
        $this->tableRepository->loadCurrentRight($table, auth()->id());
        $force = !!($replacer['force'] ?? '');

        foreach ($columns as $fld) {
            if ($table->_is_owner || in_array($fld, $table->_current_right->edit_fields->toArray()) || $force) {
                $sql = new TableDataQuery($table);

                $replace_new = $sql->changeFindStrForUserColumns($fld, $replacer['new_val'] ?? '');
                $search_new = $sql->changeFindStrForUserColumns($fld, $replacer['old_val'] ?? '');

                $header = $table->_fields->where('field', '=', $fld)->first();

                if ($search_new && $header && $header->input_type == 'Input') {
                    $updater = [$fld => DB::raw("REPLACE(LOWER(" . $fld . "), '" . strtolower($search_new) . "', '" . $replace_new . "')")];
                } else {
                    $updater = [$fld => $replace_new];
                }

                if (!$norowhash) {
                    $updater['row_hash'] = Uuid::uuid4();
                }

                $sql = $this->prepareReplaceSql($sql, [$search_new], [$fld], $request_params, true, $force);
                $sql->getQuery()->update($updater);
            }
        }
        $this->newTableVersion($table);
        return 1;
    }

    /**
     * Update Mass Check Boxes for One Table column.
     *
     * @param Table $table_info
     * @param array $row_ids
     * @param String $field
     * @param $status
     * @return int
     */
    public function updateMassCheckBoxes(Table $table_info, array $row_ids, string $field, $status)
    {
        return $this->tableDataRepository->updateMassCheckBoxes($table_info, $row_ids, $field, $status);
    }

    /**
     * Get DDL values for reference DDL.
     *
     * @param DDL $ddl
     * @param array $row
     * @param string $search
     * @param int $limit
     * @return array
     */
    public function getDDLvalues(DDL $ddl, array $row = [], string $search = '', int $limit = 200)
    {
        return $this->tableDataRepository->getDDLvalues($ddl, $row, $search, $limit);
    }

    /**
     * @param DDL $ddl
     * @param DDLReference $ref
     * @param array $row
     * @param string $search
     * @param int $limit
     * @return array
     */
    public function getRowsFromDdlReference(DDL $ddl, DDLReference $ref, array $row, string $search = '', int $limit = 200): array
    {
        return $this->tableDataRepository->getRowsFromDdlReference($ddl, $ref, $row, $search, $limit);
    }

    /**
     * Favorite Toggle Row
     *
     * @param $data
     * @return array
     */
    public function favoriteToggleRow($data)
    {
        return $this->tableDataRepository->favoriteToggleRow($data);
    }

    /**
     * Favorite Toggle All Rows
     *
     * @param Table $table
     * @param array $data
     * @param $status
     * @return array
     */
    public function toggleAllFavorites(Table $table, array $data, $status)
    {
        $all_rows = (new TableDataQuery($table))->getRowsIds($data, auth()->id());

        return $this->tableDataRepository->toggleAllFavorites($table->id, !!$status, $all_rows);
    }

    /**
     * Favorite Toggle Selected Rows
     *
     * @param Table $table
     * @param array $rows_ids
     * @param $status
     * @return array
     */
    public function toggleSelectedFavorites(Table $table, array $rows_ids, $status)
    {
        return $this->tableDataRepository->toggleAllFavorites($table->id, !!$status, $rows_ids);
    }

    /**
     * Set Default Values to fields.
     *
     * @param Table $table
     * @param array $fields
     * @param $user_id
     * @return array
     */
    public function setDefaults(Table $table, array $fields, $user_id)
    {
        return $this->tableDataRepository->setDefaults($table, $fields, $user_id);
    }

    /**
     * Show Hide Column Toggle
     *
     * @param $user_id
     * @param $table_data_ids
     * @param $is_showed
     * @return bool
     */
    public function showColumnsToggle($user_id, $table_data_ids, $is_showed)
    {
        return $this->tableDataRepository->showColumnsToggle($user_id, $table_data_ids, $is_showed);
    }

    /**
     * Change Order Column
     *
     * @param $data
     * @return array
     */
    public function changeOrderColumn($data)
    {
        return $this->tableDataRepository->changeOrderColumn($data);
    }

    /**
     * Change Row Order.
     *
     * @param Table $table
     * @param int $from_order
     * @param int $to_order
     * @param int $from_id
     * @param int $to_id
     * @return array
     */
    public function changeRowOrder(Table $table, int $from_order, int $to_order, int $from_id, int $to_id)
    {
        if ($from_order == $to_order) {
            if ($from_id == $to_id) {
                return [];//the same row
            } else {
                $this->tableDataRepository->recalcOrderRow($table, $from_order);
                return $this->tableDataRepository->changeRowOrder($table, $from_id, $to_id);
            }
        } else {
            return $this->tableDataRepository->changeRowOrder($table, $from_order, $to_order);
        }
    }

    /**
     * @param Table $table
     * @param string $fld
     * @param string $formula
     * @param array $recalc_ids
     */
    public function setDefFormulaToAll(Table $table, string $fld, string $formula, array $recalc_ids = [])
    {
        $this->tableDataRepository->setDefFormulaToAll($table, $fld, $formula);
        $this->recalcTableFormulas($table, $table->user_id, $recalc_ids);
        $this->newTableVersion($table);
        dispatch(new RecalcTableFormula($table, auth()->id(), 0));
    }

    /**
     * Recalc formulas for One|All rows in TableData.
     *
     * @param Table $table
     * @param null $user_id
     * @param array $row_ids
     * @param int $job_id
     */
    public function recalcTableFormulas(Table $table, $user_id = null, array $row_ids = [], int $job_id = 0)
    {
        $evaluator = new FormulaEvaluatorRepository($table, $user_id);
        $evaluator->recalcTableFormulas($row_ids, $job_id);
    }

    /**
     * @param Table $table
     * @param string $fld
     * @param string|null $value
     * @return Collection
     */
    public function getRowIdsForField(Table $table, string $fld, string $value = null)
    {
        return $this->dataRowsRepository->getRowIdsForField($table, $fld, $value);
    }
}