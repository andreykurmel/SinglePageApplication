<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Support\Collection;
use Vanguard\Classes\MselConvert;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserGroup;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableDataRowsRepository
{
    protected $service;
    protected $recursive_passed = [];

    /**
     * TableDataRowsRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get data from user`s table
     *
     * @param array $data ('+' is required; '-' not required):
     * [
     *  +table_id: int,
     *  +page: int,
     *  +rows_per_page: int,
     *  +row_id: int, // for getting only one row
     *  -search_words: [
     *      [
     *          type: 'and'|'or',
     *          word: string
     *      ],
     *      ...
     *  ],
     *  -search_columns: [
     *      table_field1, table_field2, ...
     *  ]
     *  -search_radius: [
     *      km: float,
     *      center_lat: float,
     *      center_long: float,
     *      field_lat: string,
     *      field_long: string
     *  ]
     *  -only_favorites: string,
     *  -applied_filters: [
     *      [
     *          id: 1,
     *          field: st,
     *          name: STATE,
     *          applied_index: 1,
     *          f_type: 'String',
     *          values: [
     *              [
     *                  +checked: 1,
     *                  +val: 123,
     *                  -show: 'user'
     *              ],
     *              ...
     *          ]
     *      ],
     *      ...
     *  ],
     *  -sort: [
     *      [
     *          field: table_field,
     *          direction: 'asc'|'desc'
     *      ],
     *      ...
     *  ],
     *  -row_groups: [
     *      [
     *          id: int,
     *          name: string,
     *          status: [0,1,2] //off,indeterm,on
     *      ],
     *  ],
     *  -special_params [
     *      - table_permission_id: int,
     *      - view_hash: string,
     *      ^ view_object: auto-calculated TableView from 'view_hash'
     *  ]
     * ]
     * @param integer|null - $user_id
     *
     * @return array:
     * [
     *  rows: array,
     *  rows_count: int,
     *  filters: array,
     *  row_group_statuses: array, //rowGroup checked/undeterm/unchecked
     *  hidden_row_ids: array,
     *  global_rows_count: int,
     *  version_hash: string,
     * ]
     */
    public function getRows(Array $data, $user_id)
    {
        ini_set('memory_limit', '256M');
        set_time_limit(60);

        $table = Table::where('id', '=', $data['table_id'])->first();
        $this->autoCorrectRowOrder($table);
        // Table's relations are loaded in TableDataQuery. So we can save 15 DB queries on each request.
        $tableDataRows = new TableDataQuery($table, false, $user_id ?: 0);
        $tableDataRows->testViewAndApplyWhereClauses($data, $user_id);

        $tableDataRows->applySorting($data['sort'] ?? []);

        $sql = $tableDataRows->getQuery();
        $rowsCount = $sql->count();

        if ($data['rows_per_page'] ?? 0) {
            $sql->offset(($data['page'] - 1) * $data['rows_per_page'])
                ->limit($data['rows_per_page']);

            $rowsData = $sql->get();
            $this->attachSpecialFields($rowsData, $table, $user_id, $tableDataRows->table_permission_id);
        } else {
            $rowsData = $sql->get();
        }

        return [
            'rows' => $rowsData->toArray(),
            'rows_count' => $rowsCount,
            'filters' => $tableDataRows->filters,
            'row_group_statuses' => $tableDataRows->row_group_statuses,
            'hidden_row_ids' => $tableDataRows->groups_hidden_row_ids,
            'global_rows_count' => (new TableDataQuery($table))->getQuery()->count(),
            'version_hash' => $table->version_hash,
        ];
    }

    /**
     * Auto-correct 'row order' for all rows.
     *
     * @param Table $table
     */
    public function autoCorrectRowOrder(Table $table)
    {
        if (!$table->is_system) {
            $tdq = new TableDataQuery($table);
            $query = $tdq->getQuery();
            $query->groupBy('row_order')
                ->havingRaw('rw > 1')
                ->selectRaw('COUNT(`row_order`) as rw');
            //present not unique 'row_order'
            if ($query->get()->count()) {
                \DB::connection($tdq->getConn($table))->statement('SET @row_number = 0;');
                \DB::connection($tdq->getConn($table))->statement('UPDATE `' . $table->db_name . '` SET `row_order` = (@row_number:=@row_number + 1) ORDER BY `row_order`,`id`');
            }
        }
    }

    /**
     * Attach special fields to table Rows.
     * (Files, Users and groups, CondFormats, RowGroups)
     *
     * @param Collection $rowsData
     * @param Table $table
     * @param int|null $user_id
     * @param int|null $table_permission_id
     * @param array $only
     * @return Collection
     */
    public function attachSpecialFields(Collection $rowsData, Table $table, int $user_id = null, int $table_permission_id = null, array $only = [])
    {
        if (!$rowsData->count()) {
            return $rowsData;
        }

        if (!$only || in_array('files', $only)) {
            $this->attachFiles($rowsData, $table);
        }

        if (!$only || in_array('favorites', $only)) {
            $this->checkFavorites($rowsData, $table, $user_id);
        }

        if (!$only || in_array('users', $only)) {
            $this->attachUsersAndUGroups($rowsData, $table);
        }

        if (!$only || in_array('groups', $only) || in_array('conds', $only)) {
            $this->attachRowGroups($rowsData, $table, $user_id);
        }
        if (!$only || in_array('conds', $only)) {
            $this->attachCondFormats($rowsData, $table, $user_id, $table_permission_id);
        }

        if (!$only || in_array('refs', $only)) {
            $this->attachRefCondsById($rowsData, $table);
        }

        if (!$only || in_array('additionals', $only)) {
            $this->attachAdditionals($rowsData);
        }

        if ($table->is_system == 2 && (!$only || in_array('corresps', $only))) {
            $this->attachCorresps($rowsData, $table);
        }

        return $rowsData;
    }

    /**
     * Additionals for Vue.js
     *
     * @param Collection $rowsData
     */
    protected function attachAdditionals(Collection $rowsData) {
        foreach ($rowsData as $row) {
            $row->_checked_row = false;
        }
    }

    /**
     * Attach files into table cells.
     *
     * @param Collection $data
     * @param \Vanguard\Models\Table\Table $table
     */
    protected function attachFiles(Collection $data, Table $table)
    {
        $files = File::where('table_id', '=', $table->id)
            ->whereIn('row_id', $data->pluck('id'))
            ->with('_table_field')
            ->get();

        if (count($files)) {
            foreach ($files as $file) {
                $row = $data->where('id', '=', $file->row_id)->first();
                if ($row && $file->_table_field) {
                    $sys_col = $file->is_img ? '_images_for_' : '_files_for_';
                    if ($row->{$sys_col . $file->_table_field->field} ?? false) {
                        $row->{$sys_col . $file->_table_field->field} = array_merge($row->{$sys_col . $file->_table_field->field}, [$file]);
                    } else {
                        $row->{$sys_col . $file->_table_field->field} = [$file];
                    }
                }
            }
        }
    }

    /**
     * Check favorite rows in result collection
     *
     * @param Collection $data
     * @param \Vanguard\Models\Table\Table $table
     * @param $user_id - integer|null
     */
    protected function checkFavorites(Collection $data, Table $table, $user_id)
    {
        $favorites = FavoriteRow::where('user_id', '=', $user_id)
            ->where('table_id', '=', $table->id)
            ->whereIn('row_id', $data->pluck('id'))
            ->get();
        foreach ($data as $row) {
            $is_favorite = $favorites->where('row_id', '=', ($row->id ?? null) )->first();
            $row->_is_favorite = $is_favorite ? 1 : 0;
        }
    }

    /**
     * Check favorite rows in result collection
     *
     * @param Collection $data
     * @param Table $table
     */
    protected function attachUsersAndUGroups(Collection $data, Table $table)
    {
        [$users, $groups] = $this->getUsersAndGroupsForAttach($data, $table);

        foreach ($data as $row) {
            $creator = $users->where('id', '=', ($row->created_by ?? null) )->first();
            if ($creator) {
                $row->_created_by = $creator->first_name . ' ' . $creator->last_name;
            }
            $modifier = $users->where('id', '=', ($row->modified_by ?? null) )->first();
            if ($modifier) {
                $row->_modified_by = $modifier->first_name . ' ' . $modifier->last_name;
            }

            //add user name if some field has type='User' (it is for system tables)
            foreach ($table->_fields as $fld) {
                if ($fld->f_type === 'User' && !in_array($fld->field, $this->service->system_fields)) {
                    $rv = (string)($row->{$fld->field} ?? '');
                    $rv = $rv && $rv[0] == '[' ? json_decode($rv, true) : [$rv];
                    $rv = array_filter($rv ?: []);
                    $_usrs = [];
                    foreach ($rv as $v) {
                        //if User or UserGroup
                        $usr = $users->where('id', '=', $v)->first();
                        $usrgroup = $groups->where('id', '=', $v)->first();
                        $_usrs[$v] = $usr ?: $usrgroup ?: null;
                    }
                    $row->{'_u_' . $fld->field} = $_usrs;
                }
            }
        }
    }

    /**
     * @param Collection $data
     * @param Table $table
     * @return array
     */
    protected function getUsersAndGroupsForAttach(Collection $data, Table $table)
    {
        $users_ids = $data->pluck('created_by')->merge($data->pluck('modified_by'));

        //merge user ids if some field has type='User' (it is for system tables)
        foreach ($table->_fields as $fld) {
            if ($fld->f_type === 'User') {
                $arrs = MselectData::convert( $data->pluck($fld->field)->toArray(), $fld->input_type );
                $users_ids = $users_ids->merge($arrs);
            }
        }

        return $this->getUsersAndGroups($users_ids);
    }

    /**
     * @param Collection $users_ids
     * @return array
     */
    public function getUsersAndGroups(Collection $users_ids)
    {
        $users_ids = $users_ids->filter(function ($id) { return !!$id; })->unique();

        $group_ids = $users_ids->filter(function ($id) { return !is_numeric($id); })
            ->map(function ($id) { return substr($id, 1); });

        $users_ids = $users_ids->filter(function ($id) { return is_numeric($id); });

        $users = User::whereIn('id', $users_ids)
            ->select($this->service->onlyNames(false))
            ->get();

        $groups = UserGroup::whereIn('id', $group_ids)
            ->select(['id','name'])
            ->get();
        $modified_gr = collect([]);
        foreach ($groups as $gr) {
            $modified_gr[] = (object)[
                'id' => '_' . $gr->id,
                'first_name' => $gr->name,
                'last_name' => '(Group)',
            ];
        }

        return [$users, $modified_gr];
    }

    /**
     * Attach RowGroups in result collection
     *
     * @param Collection $data
     * @param Table $table
     * @param int|null $user_id
     */
    protected function attachRowGroups(Collection $data, Table $table, int $user_id = null)
    {
        $sql = new TableDataQuery($table, false, $user_id ?: 0);
        $id = $sql->getSqlFld();

        $row_ids = $data->pluck('id');

        //get row ids referred to RowGroups from $data
        $_row_groups = $table->_row_groups()->get()->toArray();
        if ($row_ids->count()) {
            foreach ($_row_groups as $key => $row_group) {
                $sql->clearQuery();
                $sql->applySelectedRowGroup($row_group['id']);

                $_row_groups[$key]['_linked_row_ids'] = $sql->getQuery(false)
                    ->whereIn($id, $row_ids)
                    ->get([$id])
                    ->pluck('id')
                    ->toArray();
            }
        }

        //set applied RowGroups and CondFormats to each row
        foreach ($data as $row) {
            $_applied_row_groups = [];
            foreach ($_row_groups as $row_group) {
                if (in_array($row->id ?? '', $row_group['_linked_row_ids'] ?? [])) {
                    $_applied_row_groups[] = $row_group['id'];
                }
            }
            $row->_applied_row_groups = $_applied_row_groups;
        }
        //-----
    }

    /**
     * Attach CondFormats in result collection (Should Be called 'attachRowGroups' before!!!)
     *
     * @param Collection $data
     * @param Table $table
     * @param int|null $user_id
     * @param int|null $table_permission_id
     */
    protected function attachCondFormats(Collection $data, Table $table, int $user_id = null, int $table_permission_id = null)
    {
        //get all applied CondFormats
        $_cond_formats = $table->_cond_formats()
            ->activeForUser($user_id, $table_permission_id)
            ->get()
            ->toArray();

        //set applied RowGroups and CondFormats to each row
        foreach ($data as $row) {
            $_applied_cond_formats = [];
            foreach ($_cond_formats as $format) {
                if (!$format['table_row_group_id'] || in_array($format['table_row_group_id'], $row->_applied_row_groups ?? [])) {
                    $_applied_cond_formats[] = $format['id'];
                }
            }
            $row->_applied_cond_formats = $_applied_cond_formats;
        }
        //-----
    }

    /**
     * Attach data from other tables by IDs in Special RefConditions
     *
     * @param Collection $data
     * @param Table $table
     */
    protected function attachRefCondsById(Collection $data, Table $table)
    {
        try {
            $fields = $table->_fields()
                ->where('input_type', '!=', 'Input')
                ->whereNotNull('ddl_id')
                ->get();

            if ($fields->count()) {
                //apply for each field with DDL
                foreach ($fields as $fld) {
                    $row_ids = collect([]);
                    foreach ($data as $data_row) {
                        $row_ids = $row_ids->merge(MselConvert::getArr($data_row->{$fld->field}));
                    }

                    $ref_rows = $this->get_ref_rows($fld, $row_ids);

                    foreach ($data as $data_row) {
                        $this->add_rcs($data_row, $fld->field, $ref_rows);
                    }
                }
                //-------
            }
        } catch (\Exception $e) {}
    }

    /**
     * Attach data for CorrespTables
     *
     * @param Collection $data
     * @param Table $table
     */
    protected function attachCorresps(Collection $data, Table $table)
    {
        if ($table->db_name == 'correspondence_fields') {
            $tb_names = Table::whereIn('db_name', $data->pluck('_table')->pluck('data_table'))
                ->select(['id','db_name','name'])
                ->get();
            $fld_names = TableField::whereIn('field', $data->pluck('data_field'))
                ->whereIn('table_id', $tb_names->pluck('id'))
                ->select(['id','field','name','table_id'])
                ->get();
            $link_tbs = Table::whereIn('db_name', $data->pluck('link_table_db'))
                ->select(['id','db_name','name'])
                ->get();
            $link_flds = TableField::whereIn('field', $data->pluck('link_field_db'))
                ->whereIn('table_id', $link_tbs->pluck('id'))
                ->select(['id','field','name','table_id'])
                ->get();
            foreach ($data as $row) {
                $tb = $tb_names->where('db_name', '=', $row->_table->data_table)->first();
                $fld = $tb ? $fld_names->where('field', '=', $row->data_field)->where('table_id', '=', $tb->id)->first() : '';
                $row->__data_field = $fld ? $fld->name : '';
                $row->___data_table_id = $tb ? $tb->name : '';
                $linktb = $link_tbs->where('db_name', '=', $row->link_table_db)->first();
                $linkfld = $linktb ? $link_flds->where('field', '=', $row->link_field_db)->where('table_id', '=', $linktb->id)->first() : '';
                $row->__link_field_db = $linkfld ? $linkfld->name : '';
                $row->__link_table_db = $linktb ? $linktb->name : '';
            }
        }

        if ($table->db_name == 'correspondence_tables') {
            $tb_names = Table::whereIn('db_name', $data->pluck('data_table'))
                ->select(['id','name','db_name'])
                ->get();
            foreach ($data as $row) {
                $tb = $tb_names->where('db_name', '=', $row->data_table)->first();
                $row->__data_table = $tb ? $tb->name : '';
            }
        }
    }

    /**
     * @param Collection $fields
     * @param Table $table
     */
    public function attachRCidToUnits(Collection $fields, Table $table)
    {
        //apply for each field with DDL
        try {
            foreach ($fields as $i => $fld) {
                if ($fld->unit_ddl_id && $fld->unit) {
                    $ref_rows = $this->get_ref_rows($fld, collect([$fld->unit]), 'unit_ddl_id');
                    $this->add_rcs($fld, 'unit', $ref_rows);
                }
                if ($fld->unit_ddl_id && $fld->unit_display) {
                    $ref_rows = $this->get_ref_rows($fld, collect([$fld->unit_display]), 'unit_ddl_id');
                    $this->add_rcs($fld, 'unit_display', $ref_rows);
                }
            }
        } catch (\Exception $e) {}
        //-------
    }

    /**
     * @param TableField $field
     * @param Collection $row_vals
     * @param string $mode
     * @param int $dep
     * @return Collection
     */
    protected function recursive_search(TableField $field, Collection $row_vals, string $mode = 'ddl_id', int $dep = 1)
    {
        if ($dep >= 5) {
            return $row_vals;
        }

        if ($field->{$mode}) {
            $images = $this->get_item_images($field->{$mode});
            foreach ($row_vals as $res) {
                if (!$res->img_val) {
                    $img = $images->where('option', '=', $res->show_val)->first();
                    $res->img_val = $img && $img->image_path ? $img->image_path : $res->img_val;
                }
            }

            $references = $this->get_references($field->{$mode});
            foreach ($references as $dref) {
                //prevent cyclic links
                if (in_array($dref->id, $this->recursive_passed)) {
                    continue;
                }
                $this->recursive_passed[] = $dref->id;

                $tar_field = $dref->_target_field ? $dref->_target_field->field : 'id';
                $show_field = $dref->_show_field ? '`' . $dref->_show_field->field . '`' : '""';
                $img_field = $dref->_image_field ? '`' . $dref->_image_field->field . '`' : '""';

                $reffs = (new TableDataQuery($dref->_ref_condition->_ref_table))
                    ->getQuery()
                    ->whereIn($tar_field, $row_vals->pluck('show_val'))
                    ->selectRaw('`id`, `' . $tar_field . '` as `init_val`, ' . $show_field . ' as `show_val`, ' . $img_field . ' as `img_val`')
                    ->get();

                foreach ($row_vals as $res) {
                    $find = $reffs->where('init_val', '=', $res->show_val)->first();
                    $res->show_val = $find && $find->show_val ? $find->show_val : $res->show_val;
                    $res->img_val = $find && $find->img_val ? $find->img_val : $res->img_val;
                }

                if ($dref->_target_field ?: $dref->_show_field) {
                    $row_vals = $this->recursive_search($dref->_target_field ?: $dref->_show_field, $row_vals, 'ddl_id', $dep+1);
                }
            }
        }

        return $row_vals;
    }

    /**
     * @param TableField $field
     * @param Collection $row_vals
     * @param string $mode
     * @return Collection
     */
    protected function get_ref_rows(TableField $field, Collection $row_vals, string $mode = 'ddl_id')
    {
        $result = collect($row_vals)->map(function ($item) {
            return (object)[
                'init_val' => $item,
                'show_val' => $item,
                'img_val' => '',
            ];
        });

        $this->recursive_passed = [];
        return $this->recursive_search($field, $result, $mode);
    }

    /**
     * @param object $data_row
     * @param string $field
     * @param Collection $ref_rows
     */
    protected function add_rcs(object $data_row, string $field, Collection $ref_rows)
    {
        $rcs = ['_is_ddlid' => ['show_val'=>'']];
        foreach (MselConvert::getArr($data_row->{$field}) as $row_item) {
            $row_item = is_numeric($row_item) ? floatval($row_item) : (string)$row_item;
            $rcs[$row_item] = $ref_rows->where('init_val', '=', $row_item)->first();
        }
        $data_row->{'_rc_'.$field} = array_filter($rcs);
    }

    /**
     * @param int $ddl_id
     * @return Collection
     */
    protected function get_references(int $ddl_id)
    {
        return (new DDLRepository())->ddlReferencesWhichIds( [$ddl_id] );
    }

    /**
     * @param int $ddl_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function get_item_images(int $ddl_id)
    {
        return (new DDLRepository())->ddlItemsWhichIds( [$ddl_id] );
    }

}