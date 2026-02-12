<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vanguard\AppsModules\AppOnChangeHandler;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Helpers\ColorHelper;
use Vanguard\Jobs\DDLChangedWatcherJob;
use Vanguard\Jobs\FillFieldsForCorrespondenceTable;
use Vanguard\Jobs\WatchMirrorValues;
use Vanguard\Jobs\WatchRemoteFiles;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLReference;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Modules\ColumnAutoSizer;
use Vanguard\Modules\InheritColumnModule;
use Vanguard\Modules\Permissions\PermissionObject;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\HistoryRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableSavedFilterRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Support\PrefixSufixHelper;
use Vanguard\Support\SimilarityHelper;

class TableDataRepository
{
    protected $service;
    protected $permissionsService;

    /**
     * TableDataRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->permissionsService = new UserPermissionsService();
        $this->dbcolRepository = new ColumnAutoSizer();
    }

    /**
     * @param Table $table
     * @param int|null $row_group_id
     * @param int|null $single_id
     * @return Collection
     */
    public function getRowsByRowGroup(Table $table, int $row_group_id = null, int $single_id = null): Collection
    {
        if (!$row_group_id) {
            return collect([]);
        }

        $query = new TableDataQuery($table);
        $query->applySelectedRowGroup($row_group_id);

        $query = $query->getQuery();
        if ($single_id) {
            $query->where('id', '=', $single_id);
        }

        return $query->get();
    }

    /**
     * Get distinct values for one field.
     *
     * @param Table $table
     * @param TableField $field
     * @param int $limit
     * @return array
     */
    public function getDistinctiveField(Table $table, TableField $field, int $limit = 1000): array
    {
        $rows = $this->distRows($table, $field, $limit);

        $res = [];
        if ($field->f_type == 'User') {
            $arr = $rows->pluck($field->field)->toArray();
            $arr = MselectData::convert($arr, $field->input_type);
            [$users, $groups] = (new TableDataRowsRepository())->getUsersAndGroups(collect($arr));

            foreach ($arr as $item) {
                //if User
                $usr = $users->where('id', '=', $item)->first();
                if ($usr) {
                    $res[$item] = $usr->first_name . ($usr->last_name ? ' ' . $usr->last_name : '');
                }
                //if UserGroup
                $usrgroup = $groups->where('id', '=', $item)->first();
                if ($usrgroup) {
                    $res[$item] = $usrgroup->first_name . ($usrgroup->last_name ? ' ' . $usrgroup->last_name : '');
                }
            }

        } else {
            foreach ($rows as $row) {
                $item = $row[$field->field];
                $val = DropdownHelper::valOrShow($field->field, $row);
                $res[$item] = $val;
            }
        }

        return $res;
    }

    /**
     * @param Table $table
     * @param TableField $field
     * @param int $limit
     * @return array
     */
    public function distinctForFrontEnd(Table $table, TableField $field, int $limit = 1000): array
    {
        return $this->distRows($table, $field, $limit)
            ->map(function ($row) use ($field) {
                return DropdownHelper::forFrontEndSelect($field->field, $row);
            })
            ->toArray();
    }

    /**
     * @param Table $table
     * @param TableField $field
     * @param int $limit
     * @return Collection
     */
    protected function distRows(Table $table, TableField $field, int $limit = 1000)
    {
        $sql = new TableDataQuery($table, true);

        $rows = $sql->getQuery()
            ->distinct()
            ->select($field->field)
            ->limit($limit)
            ->get();
        return (new TableDataRowsRepository())->attachSpecialFields($rows, $table, auth()->id(), ['refs','users']);
    }

    /**
     * Save To AttachmentCell Last File Path.
     *
     * @param array $data
     * @param string $file_path_with_name
     */
    public function saveToCellLastFilePath(array $data, string $file_path_with_name)
    {
        $table_id = $data['table_id'] ?? null;
        $field_id = $data['table_field_id'] ?? null;
        $row_id = $data['row_id'] ?? null;

        if ($table_id && $field_id && $row_id) {
            $table = (new TableRepository())->getTable($table_id);
            $header = $table->_fields()->where('id', $field_id)->first();

            if ($table && $header) {
                try {
                    (new TableDataQuery($table))->getQuery()
                        ->where('id', $row_id)
                        ->update([$header->field => $file_path_with_name]);
                } catch (Exception $e) {
                    Log::error('TableDataRepository::saveToCellLastFilePath - '.$e->getMessage());
                }
            }
        }
    }

    /**
     * @param Table $table
     */
    public function updateCopiedAttachPaths(Table $table)
    {
        $attach_fields = $table->_fields()->where('f_type', '=', 'Attachment')->get();
        if ($attach_fields->count()) {
            $rows = (new TableDataQuery($table))->getQuery();
            foreach ($attach_fields as $fld) {
                $rows->whereNotNull($fld->field);
            }
            $rows = $rows->get();
            foreach ($rows as $r) {
                $updater = [];
                foreach ($attach_fields as $fld) {
                    if (preg_match('/^\d+_/i', $r->{$fld->field}) && !preg_match('/^' . $table->id . '_/i', $r->{$fld->field})) {
                        $updater[$fld->field] = preg_replace('/^\d+_/i', $table->id . '_', $r->{$fld->field});
                    }
                }
                if ($updater) {
                    (new TableDataQuery($table))->getQuery()
                        ->where('id', $r->id)
                        ->update($updater);
                }
            }
        }
    }

    /**
     * Get DDL values for reference DDL.
     *
     * @param DDL $ddl
     * @param array $row
     * @param string $search
     * @param int $limit
     * @param array $moreParams
     * @return array of items: [
     *  'value' => string,
     *  'show' => string,
     *  'description' => string,
     *  'image' => string,
     *  'color' => string,
     * ]
     */
    public function getDDLvalues(DDL $ddl, array $row = [], string $search = '', int $limit = 200, array $moreParams = []): array
    {
        if (!empty($moreParams['ddl_applied_field_id'])) {
            $moreParams['field'] = TableField::where('table_id', '=', $ddl->table_id)
                ->where('id', '=', $moreParams['ddl_applied_field_id'])
                ->first();
            $moreParams['table'] = $moreParams['field']?->_table;
        }

        $ddl->loadMissing((new DDLRepository())->ddlRelationsForGetValues());

        $results = [];
        $applied_row_groups = $row['_applied_row_groups'] ?? [];

        foreach ($ddl->_references as $ref) {
            if (count($results) < $limit) {
                //Empty TargetRowGroup or Row belong to TargetRowGroup
                if (
                    !$applied_row_groups
                    || !$ref->apply_target_row_group_id
                    || in_array($ref->apply_target_row_group_id, $applied_row_groups)
                ) {
                    $ref_rows = $this->getRowsFromDdlReference($ddl, $ref, $row, $search, $limit, false, $moreParams);
                    if ($ddl->keep_sort_order) {
                        $ref_rows = DropdownHelper::ddlSort($ddl, $ref_rows);
                    }
                    $results = array_merge($results, $ref_rows);
                }
            }
        }

        $searchArr = TableDataQuery::searchStringToArr($search);

        $ddl_items = [];
        foreach ($ddl->_items as $item) {
            $show = (string)$item->show_option ?: (string)$item->option ?: null;

            if (!$search) {
                $found = true;
            } else {
                $found = true;
                foreach ($searchArr as $part) {
                    if ($part['type'] == 'or') {
                        $found = $found || str_contains(strtolower($show), $part['word']);
                    } else {
                        $found = $found && str_contains(strtolower($show), $part['word']);
                    }
                }
            }

            if ($found) {
                //Empty TargetRowGroup or Row belong to TargetRowGroup
                if (!$item->apply_target_row_group_id || in_array($item->apply_target_row_group_id, $applied_row_groups)) {
                    //Max Selection has not been reached
                    if (!$this->maxSelectionsPresentAndReached($item->option ?: null, $item->max_selections ?: 0, $moreParams)) {
                        $ddl_items[] = [
                            'value' => (string)$item->option ?: null,
                            'show' => $show,
                            'description' => $item->description ?: null,
                            'image' => $item->image_path ?: null,
                            'color' => $item->opt_color ?: null,
                            'max_selections' => $item->max_selections ?: '',
                            'search_similarity' => strlen($search) >= 3 ? SimilarityHelper::check($search, $show) : 0,
                        ];
                    }
                }
            }
        }
        if (count($results) < $limit) {
            if ($ddl->keep_sort_order) {
                $ddl_items = DropdownHelper::ddlSort($ddl, $ddl_items);
            }
            if ($ddl->items_pos == 'before') {
                $results = array_merge($ddl_items, $results);
            } else {
                $results = array_merge($results, $ddl_items);
            }
        }

        if (strlen($search) >= 3) {
            usort($results, function ($a, $b) {
                return $b['search_similarity'] <=> $a['search_similarity']; //desc
            });
        } elseif (!$ddl->keep_sort_order) {
            $results = DropdownHelper::ddlSort($ddl, $results);
        }

        return collect($results)
            ->unique(function ($item) {
                return $item['value'] . $item['show'];
            })
            ->toArray();
    }

    /**
     * @param $value
     * @param int $maxSelections
     * @param array $moreParams
     * @return bool
     */
    protected function maxSelectionsPresentAndReached($value, int $maxSelections, array $moreParams): bool
    {
        if ($maxSelections && !empty($moreParams['field']) && !empty($moreParams['table'])) {

            $presentSelections = (new TableDataQuery($moreParams['table']))
                ->getQuery()
                ->where($moreParams['field']->field, '=', $value)
                ->count();

            return $presentSelections >= $maxSelections;
        }
        return false;
    }

    /**
     * @param DDL $ddl
     * @param DDLReference $ref
     * @param array $row
     * @param string $search
     * @param int $limit
     * @param bool $no_individ
     * @param array $moreParams
     * @return array
     */
    public function getRowsFromDdlReference(DDL $ddl, DDLReference $ref, array $row, string $search = '', int $limit = 200, bool $no_individ = false, array $moreParams = []): array
    {
        if (!$ref->ref_table()) {
            return [];
        }
        $ddlRepo = new DDLRepository();

        $target_fld = $ref->_target_field ? $ref->_target_field->field : 'id';
        $image_fld = $ref->_image_field ? $ref->_image_field->field : null;
        $color_fld = $ref->_color_field ? $ref->_color_field->field : null;
        $max_selections_fld = $ref->_max_selections_field ? $ref->_max_selections_field->field : null;
        $show_fld = $ref->_show_field ? $ref->_show_field->field : null;

        $sql = new TableDataQuery($ref->ref_table(), true);
        $sql->applyRefConditionRow($ref->_ref_condition, $row, false);
        $sql->applyRowRightsForUser(auth()->id());
        $sql = $sql->getQuery();

        $sel_columns = array_filter([$target_fld, $image_fld, $color_fld, $show_fld]);
        $sql->select($sel_columns);
        $sql->distinct();
        if ($search) {
            $searchArr = TableDataQuery::searchStringToArr($search);
            foreach ($searchArr as $part) {
                if ($part['type'] == 'or') {
                    $sql->orWhere($show_fld ?: $target_fld, 'like', "%".$part['word']."%");
                } else {
                    $sql->where($show_fld ?: $target_fld, 'like', "%".$part['word']."%");
                }
            }
        }
        if ($ddl->datas_sort) {
            $sql->orderBy($target_fld, strtolower($ddl->datas_sort) ?: 'asc');
        }

        try {
            $def_clr_img = $ref->search_in_ref_colors()->where('ref_value', '=', 'Default:')->first();
            $sql_rows = $sql->limit($limit)
                ->get()
                ->map(function ($row) use ($ref, $target_fld, $image_fld, $color_fld, $show_fld, $max_selections_fld, $def_clr_img, $no_individ, $ddlRepo, $search) {
                    $showval = $row[$show_fld ?: $target_fld] ?: null;
                    $ref_clr_img = $ref->search_in_ref_colors()->where('ref_value', '=', $showval)->first() ?: $def_clr_img;
                    $refclr = $ref_clr_img && $ref_clr_img->color
                        ? $ref_clr_img->color
                        : null;
                    $refimg = $ref_clr_img && $ref_clr_img->image_ref_path
                        ? $ref_clr_img->image_ref_path
                        : null;
                    $refmax_selections = $ref_clr_img && $ref_clr_img->max_selections
                        ? $ref_clr_img->max_selections
                        : null;

                    if (!$no_individ && $ref->has_individ_colors && !$refclr) {
                        $ddlColor = $ddlRepo->addDDLReferenceColor($ref->id, [
                            'ref_value' => $showval,
                            'color' => ColorHelper::randomHex()
                        ]);
                        $ref->_reference_clr_img->push($ddlColor);
                        $refclr = $ddlColor->color;
                    }

                    return [
                        'value' => $row[$target_fld] ?: null,
                        'show' => $showval,
                        'image' => !$no_individ && $ref->has_individ_images ? $refimg : ($row[$image_fld] ?: null),
                        'description' => null,
                        'color' => !$no_individ && $ref->has_individ_colors ? $refclr : ($row[$color_fld] ?: null),
                        'max_selections' => !$no_individ && $ref->has_individ_max_selections ? $refmax_selections : ($row[$max_selections_fld] ?: null),
                        'search_similarity' => strlen($search) >= 3 ? SimilarityHelper::check($search, $showval) : 0,
                    ];
                })
                ->filter(function ($row) use ($moreParams) {
                    return !$this->maxSelectionsPresentAndReached($row['value'], $row['max_selections'] ?: 0, $moreParams);
                })
                ->toArray();
        } catch (Exception $e) {
            //incorrect DDL Reference
            //$ref->delete();
            $sql_rows = [];
        }

        return $sql_rows;
    }

    /**
     * @param TableRefCondition $ref
     * @param array $row
     * @param array|string[] $select
     * @param int $limit
     * @return Collection
     */
    public function getReferencedRows(TableRefCondition $ref, array $row, array $select = ['id'], int $limit = 10): Collection
    {
        if (!$ref->_ref_table) {
            return collect([]);
        }
        $sql = new TableDataQuery($ref->_ref_table, true);
        $sql->applyRefConditionRow($ref, $row);
        $sql = $sql->getQuery();
        $sql->select($select);
        $sql->limit($limit);
        return $sql->get();
    }

    /**
     * Favorite Toggle Row
     *
     * @param $data
     * @return array
     */
    public function favoriteToggleRow($data)
    {
        $data['user_id'] = auth()->id();
        $favoriteRow = FavoriteRow::where('table_id', $data['table_id'])
            ->where('user_id', $data['user_id'])
            ->where('row_id', $data['row_id'])
            ->first();

        if (!$favoriteRow && $data['change']) {
            $dataSave = array_merge($data, $this->service->getModified(), $this->service->getCreated());
            FavoriteRow::create($dataSave);
        } else if ($favoriteRow && !$data['change']) {
            $favoriteRow->delete();
        }
        return ['change_status' => $data['change']];
    }

    /**
     * Favorite Toggle All Rows
     *
     * @param int $table_id
     * @param bool $status
     * @param array $rows_ids
     * @return array
     */
    public function toggleAllFavorites(int $table_id, bool $status, array $rows_ids)
    {
        $user_id = auth()->id();
        //clear all before adding
        $res = FavoriteRow::where('table_id', $table_id)
            ->where('user_id', $user_id)
            ->whereIn('row_id', $rows_ids)
            ->delete();
        //if needed -> add all rows
        if ($status) {
            $data = [];
            foreach ($rows_ids as $r_id) {
                $data[] = array_merge([
                    'table_id' => $table_id,
                    'user_id' => $user_id,
                    'row_id' => $r_id,
                ], $this->service->getModified(), $this->service->getCreated());
            }
            $res = FavoriteRow::insert($data);
        }
        return ['change_status' => $res];
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
        $dataSave = $this->service->getModified();
        $dataSave['is_showed'] = $is_showed;
        return UserHeaders::where('user_id', $user_id)
            ->whereIn('table_field_id', $table_data_ids)
            ->update($dataSave);
    }

    /**
     * Get Direct TableRows by array of row Ids.
     *
     * @param Table $table
     * @param array $rows_ids
     * @return mixed
     */
    public function getDirectMassRows(Table $table, array $rows_ids)
    {
        return (new TableDataQuery($table))
            ->getQuery()
            ->whereIn($table->db_name . '.id', $rows_ids)
            ->get();
    }

    /**
     * @param Table $table
     * @return Builder
     */
    public function getTDQ(Table $table)
    {
        return (new TableDataQuery($table))->getQuery();
    }

    /**
     * Get data from user`s table
     *
     * @param array $data
     * @param $user_id
     * @return array
     * @link TableDataRowsRepository::getRows()
     *
     */
    public function getRows(array $data, $user_id)
    {
        return (new TableDataRowsRepository())->getRows($data, $user_id);
    }

    /**
     * Get Rows Hashes.
     *
     * @param Table $table
     * @param array $row_ids
     * @return Collection|static[]
     */
    public function getRowsHashes(Table $table, array $row_ids)
    {
        if (!$table->is_system) {
            $sql = new TableDataQuery($table);

            return $sql->getQuery()
                ->whereIn('id', $row_ids)
                ->select($table->db_name . '.id', 'row_hash')
                ->get();
        } else {
            return [];
        }
    }

    /**
     * @param Table $table
     * @param PermissionObject|null $permis
     * @param string $field
     * @return Collection
     */
    public function getRowsColumn(Table $table, PermissionObject $permis = null, string $field = 'id')
    {
        $sql = new TableDataQuery($table);
        if ($permis) {
            $sql->applyRowGroups($permis->view_row_groups->toArray());
        }

        return $sql->getQuery()
            ->select($field)
            ->get()
            ->pluck($field);
    }

    /**
     * @param Table $table
     * @param array $params
     * @return bool|mixed
     */
    public function removeByParams(Table $table, array $params)
    {
        if (count($params)) {
            $sql = (new TableDataQuery($table))->getQuery();
            foreach ($params as $key => $val) {
                $sql->where($key, '=', $val);
            }
            return $sql->delete();
        } else {
            return false;
        }
    }

    /**
     * insert row into the table
     *
     * @param Table $table_info :
     * @param array $fields :
     * [
     *  table_id: int,
     *  fields: [
     *      table_field: value,
     *      ...
     *  ]
     * ]
     * @param int $user_id
     * @param int|null $is_copy
     * @return int
     */
    public function insertRow(Table $table_info, array $fields, $user_id, int $is_copy = null)
    {
        $this->rowIsUniqueCheck($table_info, $fields);

        $table_info->loadMissing('_fields');
        $fields = $this->setDefaults($table_info, $fields, $user_id, $is_copy);

        $table_info->num_rows += 1;
        Table::where('id', $table_info->id)
            ->update(['num_rows' => $table_info->num_rows]);

        $sql = (new TableDataQuery($table_info, true))->getQuery();

        $beforePrefix = array_merge(
            $this->service->delSystemFields($fields),
            $this->service->getModified($table_info),
            $this->service->getCreated($table_info)
        );
        $beforePrefix = $this->setSpecialValues($table_info, $beforePrefix);
        $beforePrefix = $this->setBeforeInsertValues($table_info, $beforePrefix);
        $beforePrefix['row_order'] = $beforePrefix['row_order'] ?? 0;
        $dataSave = $is_copy
            ? PrefixSufixHelper::applyToRow($table_info, $beforePrefix)
            : $beforePrefix;

        try {
            $row_id = $sql->insertGetId($dataSave);
        } catch (\Exception $e) {
            if (ExceptionFixer::handle($e, $table_info)) {
                $row_id = $sql->insertGetId($dataSave);
            } else {
                throw $e;
            }
        }

        $beforePrefix['_new_id'] = $row_id;
        $dataSave['id'] = $row_id;

        $after_inserts = ['row_order' => $dataSave['row_order'] ?? $row_id];//mark: new rows to the end
        (new TableDataQuery($table_info, true))->getQuery()
            ->where('id', $row_id)
            ->update($after_inserts);

        (new WatchMirrorValues($table_info->id, $dataSave))->handle();//Must be dispatched to work with Formulas!
        dispatch(new WatchRemoteFiles($table_info->id, null, $dataSave));
        if ($table_info->db_name == 'correspondence_tables') {
            dispatch(new FillFieldsForCorrespondenceTable($row_id));
        }

        $inh = new InheritColumnModule($table_info);
        if ($is_copy && $inh->hasChildren()) {
            $inh->checkForCopy($beforePrefix);
        }

        $this->service->tableModified($table_info->id);
        return $row_id;
    }

    /**
     * Check that row is unique.
     *
     * @param Table $table
     * @param array $fields
     * @param int|null $row_id
     * @throws Exception
     */
    public function rowIsUniqueCheck(Table $table, array $fields, int $row_id = null)
    {
        if ($table->_unique_fields->count()) {
            $query = (new TableDataQuery($table, true))->getQuery();
            //apply unique clause
            foreach ($table->_unique_fields as $header) {
                $query->where($header->field, $fields[$header->field] ?? null);
            }
            //exclude updated row id
            if ($row_id) {
                $query->where('id', '!=', $row_id);
            }
            //check that row with these columns already present
            if ($query->count() > 0) {
                $uniq_fields = $table->_unique_fields->pluck('name')->toArray();
                throw new Exception('Field value or the combination of field values "' . implode(', ', $uniq_fields) . '" must be unique!', 1);
            }
        }
    }

    /**
     * Set Default Values to fields.
     *
     * @param Table $table
     * @param array $fields
     * @param int|null $user_id
     * @param int|null $is_copy
     * @return array
     */
    public function setDefaults(Table $table, array $fields, int $user_id = null, int $is_copy = null)
    {
        if (!empty($fields['_defaults_applied'])) {
            $fields = $this->setDefaultsBySpecialRules($table, $fields, $user_id);
            return $fields;
        }

        //Set DCR or UserRight defaults
        $defa_fields = TableRights::permissions($table)->default_values;
        if ($defa_fields) {
            $fields = $this->fillFieldsByPermis($fields, $defa_fields);
        }

        $fields = $this->setDefaultsBySpecialRules($table, $fields, $user_id);

        $fields = $this->setDefaultsFromColumnDatabase($table, $fields);

        $fields = $this->service->setDefaultAutoValues($table, $fields, $is_copy);

        $fields['_defaults_applied'] = true;

        return $fields;
    }

    /**
     * Defaults from special rules.
     *
     * @param Table $table
     * @param array $fields
     * @param $user_id
     * @return array
     */
    protected function setDefaultsBySpecialRules(Table $table, array $fields, $user_id)
    {
        if ($user_id && $table->user_id != $user_id) {
            //set Defaults from UserRights
            foreach ($table->_fields as $header) {
                if ($header->f_type == 'User' && empty($fields[$header->field])) {
                    $fields[$header->field] = auth()->id();
                }
            }
        }
        if ($table->db_name == 'correspondence_tables') {
            $fields['active'] = empty($fields['active']) ? 0 : 1;

            $fields['data_table'] = $fields['data_table'] ?? '';
            $tble = (new TableRepository())->getTableByDB($fields['data_table']);
            $fields['data_table'] = $tble ? $tble->db_name : $fields['data_table'];
            $fields['__data_table'] = $tble ? $tble->name : '';
        }
        if ($table->db_name == 'correspondence_fields') {
            if (empty($fields['app_field']) && !empty($fields['data_field'])) {
                $corrTable = CorrespTable::find($fields['correspondence_table_id'] ?? null);
                $tble = (new TableRepository())->findByDbOrId($corrTable ? $corrTable->data_table : null);
                $fld = TableField::where('field', $fields['data_field'])
                    ->where('table_id', '=', $tble ? $tble->id : null)
                    ->first();

                $fldVal = $fld ? preg_replace('/[\s,]+/i', '_', $fld->name) : null;
                $fldVal = preg_replace('/[^\w\d_]/i', '', $fldVal);
                $fields['app_field'] = strtolower($fldVal);
            }

            $fields['link_table_db'] = $fields['link_table_db'] ?? null;
            $tble = (new TableRepository())->findByDbOrId($fields['link_table_db']);
            $fields['link_table_db'] = $tble ? $tble->db_name : $fields['link_table_db'];
            $fields['__link_table_db'] = $tble ? $tble->name : '';
        }
        if ($table->db_name == 'promo_codes' && ($fields['_changed_field'] ?? '') != 'is_active') {
            $fields['start_at'] = $fields['start_at'] ?? now()->toDateString();
            $fields['end_at'] = $fields['end_at'] ?? now()->addMonth()->toDateString();
            $fields['is_active'] = now()->toDateString() >= $fields['start_at']
                && now()->toDateString() <= $fields['end_at'];
        }
        return $fields;
    }

    /**
     * @param array $fields
     * @param Collection $defa_fields
     * @return array
     */
    protected function fillFieldsByPermis(array $fields, Collection $defa_fields)
    {
        foreach ($defa_fields as $field => $default) {
            $fld_key = $default['input_type'] !== 'Formula'
                ? $field
                : $field . '_formula';

            $should_set = empty($fields[$fld_key]) || Str::startsWith($fields[$fld_key], '{$');
            if ((string)$default['default'] && $should_set) {
                $fields[$fld_key] = $this->parseDefval($default['default']);
            }
        }
        return $fields;
    }

    /**
     * @param $def_val
     * @return string
     */
    protected function parseDefval($def_val)
    {
        $user = auth()->user();
        switch ($def_val) {
            case '{$first_name}':
                $def = $user ? $user->first_name : '';
                break;
            case '{$last_name}':
                $def = $user ? $user->last_name : '';
                break;
            case '{$email}':
                $def = $user ? $user->email : '';
                break;
            case '{$user}':
                $def = $user ? $user->id : '';
                break;
            default:
                $def = $def_val;
        }
        return $def;
    }

    /**
     * @param Table $table
     * @param array $fields
     * @return array
     */
    protected function setDefaultsFromColumnDatabase(Table $table, array $fields)
    {
        //set Defaults from Column DataBase
        foreach ($table->_fields as $col) {
            $col_val = isset($fields[$col->field]) ? (string)$fields[$col->field] : '';
            if (!strlen($col_val) && strlen($col->f_default)) {
                $fields[$col->field] = $this->parseDefval($col->f_default);
            }
            $frm_key = $col->field . '_formula';
            $frm_val = isset($fields[$frm_key]) ? (string)$fields[$frm_key] : '';
            if ($col->input_type === 'Formula' && !strlen($frm_val)) {
                $fields[$frm_key] = ($col->f_formula);
            }
        }
        return $fields;
    }

    /**
     * @param Table $table
     * @param array $fields
     * @return array
     * @throws Exception
     */
    public function setSpecialValues(Table $table, array $fields)//NOTE: before each row insert/update
    {
        $fields['row_hash'] = Uuid::uuid4();
        $present_keys = array_keys($fields);
        $all_len = 0;
        foreach ($table->_fields as $hdr) {
            if (in_array($hdr->field, $present_keys) && !strlen($fields[$hdr->field])) {
                $fields[$hdr->field] = $this->service->isStringType($hdr->f_type)
                    ? '' //String types can be "", not NULL!
                    : null;
            }

            $val = $fields[$hdr->field] ?? '';
            $frmla = $fields[$hdr->field . '_formula'] ?? '';
            $all_len += $this->dbcolRepository->increaseIfNeeded($table, $hdr, $val, $frmla);
        }
        if ($all_len > 65535) {
            throw new Exception('Table Row Size more 65535 (without Text columns) table_id:' . $table->id, 1);
        }
        return $fields;
    }

    /**
     * @param Table $table
     * @param array $fields
     * @return array
     * @throws Exception
     */
    public function setBeforeInsertValues(Table $table, array $fields): array
    {
        if ($table->_srv_url && empty($fields[$table->_srv_url->field])) {
            $fields[$table->_srv_url->field] = Uuid::uuid4();
        }
        if (!$table->is_system) {
            $fields['static_hash'] = Uuid::uuid4();
        }
        return $fields;
    }

    /**
     * @param Table $table
     * @param string $fld
     * @param string $formula
     * @return int
     */
    public function setDefFormulaToAll(Table $table, string $fld, string $formula)
    {
        return (new TableDataQuery($table))->getQuery()->update([
            $fld . '_formula' => $formula,
        ]);
    }

    /**
     * @param Table $table
     * @param TableField $header
     * @return int
     */
    public function updateDatabaseDefaults(Table $table, TableField $header)
    {
        if (strlen($header->f_default)) {
            return (new TableDataQuery($table))
                ->getQuery()
                ->where($header->field, '=', '')
                ->orWhereNull($header->field)
                ->update([
                    $header->field => $header->f_default
                ]);
        } else {
            return 0;
        }
    }

    /**
     * @param Table $table
     * @return mixed
     */
    public function tableUserFields(Table $table)
    {
        return $table->_fields()
            ->where('f_type', 'User')
            ->get(['field'])
            ->pluck('field')
            ->toArray();
    }

    /**
     * @param Table $table
     * @param array $rows
     * @return array
     * @throws Exception
     */
    public function insertMass(Table $table, array $rows): array
    {
        $max_id = (new TableDataQuery($table))->getQuery()
            ->orderBy('id', 'desc')
            ->first(['id']);
        $max_id = ($max_id ? $max_id->id + 1 : 1);

        foreach ($rows as $r_index => $row) {
            $dataSave['_defaults_applied'] = true;
            $dataSave = $this->setDefaults($table, $dataSave, auth()->id());
            $dataSave = array_merge(
                $this->service->delSystemFields($row),
                $this->service->getModified($table),
                $this->service->getCreated($table)
            );
            $dataSave = $this->setSpecialValues($table, $dataSave);
            $dataSave = $this->setBeforeInsertValues($table, $dataSave);
            $dataSave['row_order'] = $max_id + $r_index;
            $rows[$r_index] = $dataSave;
        }

        $sql = new TableDataQuery($table);
        $sql->getQuery()->insert($rows);
        $this->service->tableModified($table->id);

        //new ids
        return $sql->getQuery()
            ->where('id', '>', $max_id)
            ->orderBy('id')
            ->select(['id'])
            ->get()
            ->pluck('id')
            ->toArray();
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
     * @param int $user_id
     * @param array $extra
     * @param int $lvl
     *
     * @return bool|null
     * @throws Exception
     */
    public function updateRow(Table $table_info, $row_id, array $fields, $user_id, array $extra = [], int $lvl = 1)
    {
        $this->rowIsUniqueCheck($table_info, $fields, $row_id);

        $dataQuery = (new TableDataQuery($table_info, true));
        $sql = $dataQuery->getQuery();

        $old_row = $sql->where($dataQuery->getSqlFld(), '=', $row_id)->first();
        $old_row = $old_row ? $old_row->toArray() : [];

        $new_row = array_merge($old_row, $fields);
        //$new_row = $this->setDefaults($table_info, $new_row, $user_id);
        if (!isset($extra['nohandler'])) {
            [$new_row, $appld] = (new AppOnChangeHandler($table_info))->testRow($new_row, $old_row);
        }

        $data_fields = $this->storeInHistory($table_info, $old_row, $row_id, $new_row, $user_id);
        $result = $this->updateWithRowHash($table_info, $row_id, $new_row);

        if (empty($extra['nowatchers'])) {
            dispatch(new DDLChangedWatcherJob($table_info->id, $new_row, $old_row));
            dispatch(new WatchRemoteFiles($table_info->id, null, $new_row, $old_row));
            (new WatchMirrorValues($table_info->id, $new_row))->handle();
        }

        $inh = new InheritColumnModule($table_info);
        if ($inh->hasChildren(true)) {
            $inh->checkForUpdate($old_row, $new_row, $lvl);
        }

        return $result;
    }

    /**
     * Store values in History.
     *
     * @param Table $table_info
     * @param array $old_row
     * @param $row_id
     * @param array $fields
     * @param $user_id
     * @return array
     */
    protected function storeInHistory(Table $table_info, array $old_row, $row_id, array $fields, int $user_id = null)
    {
        $user_id = $user_id ?: $table_info->user_id;

        if (!$table_info->_all_fields) {
            //optimisation of a lot calls of updateRow ($table->_fields can be different because User's permissions)
            $table_info->_all_fields = $table_info->_fields()->get();
        }
        $historyRepo = new HistoryRepository();

        $data_fields = $this->service->delSystemFields($fields);
        foreach ($data_fields as $key => $new_value) {
            $field_for_id = $table_info->_all_fields->where('field', '=', $key)->first();
            if ($field_for_id) {

                $cansave = $table_info->enabled_activities || $field_for_id->show_history == 1;
                $old_val = $old_row[$key] ?? null;

                if ($old_val != $new_value && $cansave && $user_id) {
                    $historyRepo->store($table_info, $user_id, $field_for_id->id, $row_id, $old_val);
                }

            } else {
                if (!preg_match('/(_formula|_sys)$/i', $key)) {
                    //unset all not found columns except of 'Formulas' and 'Systems'
                    unset($data_fields[$key]);
                }
            }
        }
        return $data_fields;
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @param array $fields
     * @return int
     */
    protected function updateWithRowHash(Table $table, int $row_id, array $fields)
    {
        if (!$table->is_system) {
            $fields = array_merge($fields, $this->service->getModified($table));
            $fields = $this->setSpecialValues($table, $fields);
        } else {
            $fields = $fields ? $this->setDefaults($table, $fields, null) : [];
        }

        return $this->sqlUpdateTdq($table, $row_id, $fields);
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @param array $fields
     * @return int
     */
    protected function sqlUpdateTdq(Table $table, int $row_id, array $fields): int
    {
        $this->service->tableModified($table->id);
        if ($fields && $row_id) {
            $tdq = (new TableDataQuery($table, true));
            $query = $tdq->getQuery(false)->where($tdq->getSqlFld(), '=', $row_id);

            try {
                return $query->update($this->service->delSystemFields($fields, true));
            } catch (\Exception $e) {
                if (ExceptionFixer::handle($e, $table)) {
                    return $query->update($this->service->delSystemFields($fields, true));
                } else {
                    throw $e;
                }
            }
        }
        return 0;
    }

    /**
     * @param Table $table_info
     * @param int $row_id
     * @param array $fields
     * @param bool $with_hash
     * @return int
     */
    public function quickUpdate(Table $table_info, int $row_id, array $fields = [], bool $with_hash = true)
    {
        if ($with_hash) {
            return $this->updateWithRowHash($table_info, $row_id, $fields);
        } else {
            return $this->sqlUpdateTdq($table_info, $row_id, $fields);
        }
    }

    /**
     * save new Row in DB.
     *
     * @param Table $table
     * @param array $updated_row
     * @return mixed
     */
    public function saveInDbTempRow(Table $table, array $updated_row)
    {
        $sql = (new TableDataQuery($table, true))
            ->getQuery(false)
            ->where('row_hash', $this->service->sys_row_hash['cf_temp']);

        $upd_data = $this->setSpecialValues($table, $updated_row);
        $upd_data = $this->service->delSystemFields($upd_data);
        $upd_data = $this->service->clearAllAutoNumbers($table, $upd_data);
        $upd_data['row_hash'] = $this->service->sys_row_hash['cf_temp'];

        if ($sql->count()) {
            $sql->update($upd_data);
        } else {
            $sql->insert($upd_data);
        }

        $row = $sql->first();
        (new WatchMirrorValues($table->id, $row->toArray()))->handle();

        return $sql->first();
    }

    /**
     * Delete row by id
     *
     * @param Table $table_info
     * @param int $row_id :
     *
     * @return bool|null
     */
    public function deleteRow(Table $table_info, int $row_id)
    {
        $oldrow = $this->getDirectRow($table_info, $row_id);

        $table_info->num_rows -= 1;
        Table::where('id', $table_info->id)
            ->update(['num_rows' => $table_info->num_rows]);

        $sql = new TableDataQuery($table_info, true);

        $res = $sql->getQuery()
            ->where($sql->getSqlFld(), '=', $row_id)
            ->delete();

        if ($oldrow) {
            dispatch(new WatchMirrorValues($table_info->id, $oldrow->toArray()));
            dispatch(new WatchRemoteFiles($table_info->id, null, $oldrow->toArray(), [], true));

            $inh = new InheritColumnModule($table_info);
            if ($inh->hasChildren()) {
                $inh->checkForDelete($oldrow->toArray());
            }
        }

        $this->service->tableModified($table_info->id);
        return $res;
    }

    /**
     * Get Direct TableRow by Id.
     *
     * @param Table $table
     * @param int $row_id
     * @return TableData|null
     */
    public function getDirectRow(Table $table, int $row_id = null)
    {
        $sql = (new TableDataQuery($table))->getQuery(!!$table->is_system);
        if ($row_id) {
            $sql->where($table->db_name . '.id', $row_id);
        } else {
            $sql->where($table->db_name . '.row_hash', $this->service->sys_row_hash['cf_temp']);
        }
        return $sql->first();
    }

    /**
     * @param Table $table
     * @param string $field
     * @param string $row_hash
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getRowBy(Table $table, string $field, string $row_hash)
    {
        return (new TableDataQuery($table))
            ->getQuery(!!$table->is_system)
            ->where($field, $row_hash)
            ->first();
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
        $this->service->tableModified($table_info->id);
        $sql = new TableDataQuery($table_info);
        $dataSave = array_merge([$field => $status], $this->service->getModified($table_info));
        return $sql->getQuery()
            ->whereIn('id', $row_ids)
            ->update($dataSave);
    }

    /**
     * Get All rows
     *
     * @param Table $table_info
     * @param array $data : -> similar to getRows() method ->
     * @param int $user_id :
     *
     * @return Builder
     */
    public function getAllRowsSql(Table $table_info, array $data, $user_id): Builder
    {
        $sql = new TableDataQuery($table_info, true, $user_id);

        //apply searching, filtering, row rules to getRows
        $sql->applyWhereClause($data, $user_id);

        return $sql->getQuery();
    }

    /**
     * Delete rows
     *
     * @param Table $table_info
     * @param array $data : -> similar to getRows() method ->
     * @param int $user_id :
     *
     * @return array
     */
    public function deleteAllRows(Table $table_info, array $data, $user_id)
    {
        $sql = new TableDataQuery($table_info, true, $user_id);

        //apply searching, filtering, row rules to getRows
        $sql->applyWhereClause($data, $user_id);
        $sql = $sql->getQuery();

        $inh = new InheritColumnModule($table_info);
        if ($inh->hasChildren()) {
            (clone $sql)->chunk(100, function ($old_rows) use ($inh) {
                foreach ($old_rows as $old_row) {
                    $inh->checkForDelete($old_row->toArray());
                }
            });
        }

        $all_ids = (clone $sql)->select('id')->get()->pluck('id')->toArray();
        $sql->delete();

        dispatch(new WatchMirrorValues($table_info->id));
        dispatch(new WatchRemoteFiles($table_info->id, null, [], [], true));

        $this->service->tableModified($table_info->id);
        return $all_ids;
    }

    /**
     * Delete rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @param int $lvl
     * @return bool
     */
    public function deleteSelectedRows(Table $table, array $rows_ids, int $lvl = 1)
    {
        if (!$rows_ids) {
            return false;
        }

        $sql = new TableDataQuery($table, true);
        $sql = $sql->getQuery()->whereIn('id', $rows_ids);

        $inh = new InheritColumnModule($table);
        if ($inh->hasChildren()) {
            (clone $sql)->chunk(100, function ($old_rows) use ($inh, $lvl) {
                foreach ($old_rows as $old_row) {
                    $inh->checkForDelete($old_row->toArray(), $lvl);
                }
            });
        }

        $res = $sql->delete();

        dispatch(new WatchMirrorValues($table->id));
        dispatch(new WatchRemoteFiles($table->id));

        $this->service->tableModified($table->id);
        return $res;
    }

    /**
     * Mass Copy rows by ids
     *
     * @param Table $table
     * @param array $rows_ids
     * @param array $only_columns
     * @param int $lvl
     * @return array : [from_id => to_id]
     */
    public function massCopy(Table $table, array $rows_ids, array $only_columns = [], int $lvl = 1)
    {
        $sql = new TableDataQuery($table);
        $rows = $sql->getQuery()
            ->whereIn($sql->getSqlFld(), $rows_ids)
            ->get()
            ->toArray();

        $added_ids = [];

        $only_columns = array_filter($only_columns);
        //copy formulas also
        $only_columns = array_merge($only_columns, array_map(function ($i) {
            return $i . '_formula';
        }, $only_columns));

        $datas = [];
        $sql = new TableDataQuery($table, true);
        foreach ($rows as $row) {
            $beforePrefix = !empty($only_columns)
                ? collect($row)->only($only_columns)->toArray()
                : $row;
            $beforePrefix = $this->service->delSystemFields($beforePrefix);
            $beforePrefix = array_merge($beforePrefix, $this->service->getModified($table), $this->service->getCreated($table));
            $beforePrefix = $this->setSpecialValues($table, $beforePrefix);
            $beforePrefix = $this->setBeforeInsertValues($table, $beforePrefix);
            $beforePrefix = $this->service->setDefaultAutoValues($table, $beforePrefix, true);
            $data = PrefixSufixHelper::applyToRow($table, $beforePrefix);
            $new_row_id = $sql->getQuery()->insertGetId($data);

            if ($new_row_id) {
                //NOTE: new rows to the end
                (clone $sql->getQuery())
                    ->where($sql->getSqlFld(), '=', $new_row_id)
                    ->update(['row_order' => $new_row_id]);
                $added_ids[$row['id']] = $new_row_id;

                $beforePrefix['id'] = $row['id'];
                $beforePrefix['_new_id'] = $new_row_id;
                $datas[] = $beforePrefix;
            }
        }

        $mirror = count($rows) == 1 ? Arr::first($rows) : [];
        (new WatchMirrorValues($table->id, $mirror))->handle();
        dispatch(new WatchRemoteFiles($table->id, null, $mirror));

        $inh = new InheritColumnModule($table);
        if ($inh->hasChildren()) {
            foreach ($datas as $data) {
                $inh->checkForCopy($data, $lvl);
            }
        }

        $this->service->tableModified($table->id);
        return $added_ids;
    }

    /**
     * Change Order Column
     *
     * @param $data
     * @return array
     */
    public function changeOrderColumn($data)
    {
        $selectId = (int)$data['select_user_header_id'];
        $targetId = (int)$data['target_user_header_id'];

        $table = Table::where('id', $data['table_id'])->first();
        $tableFildIds = TableField::where('table_id', $data['table_id'])->select('id')->get();

        if ($table->user_id != auth()->id()) {
            $this->reorderSysHeaders($tableFildIds);

            $orders = UserHeaders::whereIn('table_field_id', $tableFildIds)
                ->where('user_id', auth()->id());
        } else {
            $orders = TableField::whereIn('id', $tableFildIds);
        }

        $orders = $orders->orderBy('order')
            ->get()
            ->pluck('id')
            ->toArray();

        unset ($orders[array_search($selectId, $orders)]);

        $counter = 0;
        $newOrders = [];
        foreach ($orders as $order => $id) {
            if ($id == $targetId) {
                $newOrders[++$counter] = $selectId;
            }
            $newOrders[++$counter] = $id;
        }

        foreach ($newOrders as $order => $id) {
            if ($table->user_id != auth()->id()) {
                UserHeaders::where('id', $id)->update(['order' => $order]);
            } else {
                TableField::where('id', $id)->update(['order' => $order]);
            }
        }

        return ['change_status' => 1];
    }

    /**
     * @param Collection $tableFildIds
     */
    protected function reorderSysHeaders(Collection $tableFildIds)
    {
        $sys_hdrs = UserHeaders::whereHas('_field', function ($q) {
            $q->whereIn('field', $this->service->c2m2_fields);
        })
            ->whereIn('table_field_id', $tableFildIds)
            ->where('user_id', auth()->id())
            ->get();
        foreach ($sys_hdrs as $hdr) {
            $hdr->update([
                'order' => count($tableFildIds)
            ]);
        }
    }

    /**
     * Change Row Order.
     *
     * @param Table $table
     * @param int $from_id
     * @param int $to_id
     * @return array
     */
    public function changeRowOrder(Table $table, int $from_id, int $to_id)
    {
        $orders = (new TableDataQuery($table))->getQuery()
            ->where('row_order', '<=', max($from_id, $to_id))
            ->where('row_order', '>=', min($from_id, $to_id))
            ->orderBy('row_order')
            ->get();

        $changer = ($from_id > $to_id ? 1 : -1);
        foreach ($orders as $order) {
            if ($order->row_order == $from_id) {
                $this->queryUpdateOrder($table, $order->id, $to_id);
            } else {
                $this->queryUpdateOrder($table, $order->id, $order->row_order + $changer);
            }
        }

        return ['change_status' => 1];
    }

    /**
     * @param Table $table
     * @param $order_id
     * @param $new_order
     * @return int
     */
    protected function queryUpdateOrder(Table $table, $order_id, $new_order)
    {
        return (new TableDataQuery($table))->getQuery()
            ->where('id', '=', $order_id)
            ->update([
                'row_order' => $new_order
            ]);
    }

    /**
     * Recalc Order Row.
     *
     * @param Table $table
     * @param int $order_recalc
     */
    public function recalcOrderRow($table, int $order_recalc)
    {
        if (!($table instanceof Table)) {
            $table = (new TableRepository())->getTableByDB($table);
        }
        (new TableDataQuery($table, true))
            ->getQuery()
            ->where('row_order', '=', $order_recalc)
            ->update(['row_order' => DB::raw('`id`')]);
    }

    /**
     * Get SQL for loading Chart Data.
     *
     * @param Table $table
     * @param array $request_params
     * @param array $excluded_vals // ['verts','hors']
     * @param string $data_range
     * @return Builder|TableDataQuery
     */
    public function getSqlForChart(Table $table, array $request_params, array $excluded_vals, string $data_range)
    {
        if (!empty($request_params['link']) && !empty($request_params['table_row'])) {
            $sql = (new TableDataService())->fieldRowsSql($table, $request_params['link'], $request_params['table_row']);
        } else {
            //prepare table SQL
            $sql = new TableDataQuery($table, true, auth()->id());
            //apply searching, filtering, row rules to getRows
            $sql->testViewAndApplyWhereClauses($request_params, auth()->id());
            $sql->applySorting($request_params['sort'] ?? []);
            $sql->checkAndApplyDataRange($data_range ?: '0');
            $sql = $sql->getQuery();
            if (!empty($request_params['rows_per_page'])) {
                $sql->limit($request_params['rows_per_page']);
            }
        }

        //apply excluded values
        $this->applyExcluded($sql, $excluded_vals['hors']);
        $this->applyExcluded($sql, $excluded_vals['verts']);
        //apply excluded values ^^^

        return $sql;
    }

    /**
     * @param Builder $sql
     * @param array $excluded_pairs : ['db_name1' => [v1,v2,v3], 'db_name2' => [q1,q2,q3]]
     */
    protected function applyExcluded(Builder $sql, array $excluded_pairs)
    {
        $fld1 = array_keys($excluded_pairs)[0] ?? '';
        $fld2 = array_keys($excluded_pairs)[1] ?? '';
        $values1 = $excluded_pairs[$fld1] ?? [];
        $values2 = $excluded_pairs[$fld2] ?? [];

        if ($fld1 && $fld2 && $values1 && $values2) {
            foreach ($values1 as $idx => $n) {
                $v1 = is_null($values1[$idx]) ? '' : $values1[$idx];
                $v2 = is_null($values2[$idx]) ? '' : $values2[$idx];
                $sql->where(function (Builder $inner) use ($fld1, $fld2, $v1, $v2) {
                    $inner->orWhere($fld1, '!=', $v1);
                    $inner->orWhere($fld2, '!=', $v2);
                });
            }
        } elseif ($fld1 && $values1) {
            $v = array_map(function ($el) {
                return is_null($el) ? '' : $el;
            }, $values1);
            $sql->whereNotIn($fld1, $v);
        }
    }

    /**
     * Get filters as independent part.
     *
     * @param int $table_id
     * @param array $data
     * @param int|null $as_user_id
     * @return array
     */
    public function getFilters(int $table_id, array $data = [], int $as_user_id = null)
    {
        $table = Table::find($table_id);
        $sql = new TableDataQuery($table, false, $as_user_id);
        return (new TableDataFiltersModule($sql))
            ->getFilters($data);
    }

    /**
     * @param TableField $field
     * @param TableRefCondition $ref_cond
     * @param $old_val
     * @param $new_val
     */
    public function updateFromChangedDDL(TableField $field, TableRefCondition $ref_cond, $old_val, $new_val)
    {
        $sql = new TableDataQuery($field->_table);
        //$sql->getRowsForRefCondition($ref_cond);
        $sql->getQuery()
            ->where($field->field, '=', $old_val)
            ->update([
                $field->field => $new_val,
                'row_hash' => Uuid::uuid4(),
            ]);

        //update hashes
        (new TableRepository())->onlyUpdateTable($field->_table->id, [
            'version_hash' => Uuid::uuid4()
        ]);
    }

    /**
     * @param Table $table
     * @return int
     */
    public function count(Table $table): int
    {
        return (new TableDataQuery($table, true))
            ->getQuery()
            ->count();
    }

    /**
     * Get Column Size.
     *
     * @param array $col
     * @return int
     */
    protected function colSize($col)
    {
        $res = 255;
        if ($col) {
            switch ($col->f_type) {
                case 'Integer':
                case 'String':
                    $res = $col->f_size ?: 255;
                    break;
                case 'Decimal':
                case 'Currency':
                case 'Percentage':
                    $res = str_replace(',', '.', $col->f_size);
                    $res = array_sum(explode('.', $col->f_size)) + 1;
                    $res = $res > 1 ? $res : 255;
                    break;
            }
        }
        return $res;
    }
}