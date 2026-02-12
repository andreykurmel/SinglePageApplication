<?php

namespace Vanguard\Repositories\Tablda\TableData;

use DB;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Vanguard\Classes\MselConvert;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\File;
use Vanguard\Models\RemoteFile;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserGroup;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableSavedFilterRepository;
use Vanguard\Repositories\Tablda\TwilioHistoryRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Support\SimilarityHelper;
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
     *  hidden_row_groups: int[],
     *  global_rows_count: int,
     *  version_hash: string,
     * ]
     */
    public function getRows(array $data, $user_id)
    {
        ini_set('memory_limit', '256M');

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
            $this->attachSpecialFields($rowsData, $table, $user_id);
        } else {
            $rowsData = $sql->limit(500)->get();
        }

        if (!empty($data['search_words']) && !empty($data['search_columns'])) {
            $searchArray = is_array($data['search_words']) ? $data['search_words'] : TableDataQuery::searchStringToArr($data['search_words']);
            $rowsData = $this->sortBySimilarity($rowsData, $data['search_columns'], $searchArray);
        }

        return [
            'rows' => $rowsData->toArray(),
            'rows_count' => $rowsCount,
            'filters' => $tableDataRows->filters,
            'row_group_statuses' => $tableDataRows->row_group_statuses,
            'hidden_row_groups' => $tableDataRows->hidden_row_groups,
            'global_rows_count' => (new TableDataQuery($table))->getQuery()->count(),
            'version_hash' => $table->version_hash,
        ];
    }

    /**
     * @param Collection $rowsData
     * @param array $searchColumns
     * @param array $searchWords
     * @return Collection
     */
    protected function sortBySimilarity(Collection $rowsData, array $searchColumns, array $searchWords): Collection
    {
        foreach ($rowsData as $row) {
            foreach ($searchColumns as $column) {
                foreach ($searchWords as $srch) {
                    if (! $row->_search_similarity) {
                        $row->_search_similarity = 0;
                    }
                    $row->_search_similarity += SimilarityHelper::check($row[$column] ?? '', $srch['word'] ?? '');
                }
            }
        }
        return $rowsData->sortBy('_search_similarity', SORT_NUMERIC, true);
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
            $action_needed = (clone $query)->where('row_order', '=', 0)->count();
            if (!$action_needed) {
                $action_needed = (clone $query)->groupBy('row_order')
                    ->havingRaw('`rw` > 1')
                    ->selectRaw('COUNT(`row_order`) as `rw`')
                    ->first();
            }
            //present not unique 'row_order'
            if ($action_needed) {
                DB::connection(TableDataQuery::getConn($table))->statement('SET @row_number = 0;');
                DB::connection(TableDataQuery::getConn($table))->statement('UPDATE `' . $table->db_name . '` SET `row_order` = (@row_number:=@row_number + 1) ORDER BY `row_order`,`id`');
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
     * @param array $only
     * @return Collection
     */
    public function attachSpecialFields(Collection $rowsData, Table $table, int $user_id = null, array $only = [])
    {
        if (!$rowsData->count()) {
            return $rowsData;
        }

        if (!$only || in_array('twilio_history', $only)) {
            $this->attachTwilioHistory($rowsData, $table);
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
            $this->attachCondFormats($rowsData, $table, $user_id);
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
     * Attach Twilio History into table cells.
     *
     * @param Collection $allRows
     * @param Table $table
     */
    protected function attachTwilioHistory(Collection $allRows, Table $table)
    {
        $twHistory = new TwilioHistoryRepository();
        foreach ($table->_fields as $fld) {
            if (
                (in_array($fld->f_type, ['Email']) && ($fld->twilio_google_acc_id || $fld->twilio_sendgrid_acc_id))
                ||
                (in_array($fld->f_type, ['Phone Number']) && ($fld->twilio_sms_acc_id || $fld->twilio_voice_acc_id))
            ) {
                $row_ids = $allRows->pluck('id')->toArray();
                $histories = $twHistory->getForRows($table->id, $fld->id, $row_ids);

                foreach ($allRows as $row) {
                    $array = $histories->where('row_id', '=', $row['id'])
                        ->groupBy('type')
                        ->toArray();
                    $row['_tw_' . $fld->field] = $twHistory->allTypes($array);
                }
            }
        }
    }

    /**
     * Attach files into table cells.
     *
     * @param Collection $allRows
     * @param Table $table
     */
    protected function attachFiles(Collection $allRows, Table $table)
    {
        //prepare containers
        foreach ($allRows as $row) {
            foreach ($table->_fields as $header) {
                if ($header->f_type == 'Attachment') {
                    $row['_images_for_' . $header->field] = [];
                    $row['_files_for_' . $header->field] = [];
                }
            }
        }

        //attach files
        $files = File::where('table_id', '=', $table->id)
            ->whereIn('row_id', $allRows->pluck('id'))
            ->with('_table_field')
            ->get();

        foreach ($files as $file) {
            $row = $allRows->where('id', '=', $file->row_id)->first();
            if ($row && $file->_table_field) {
                $sys_col = $file->is_img ? '_images_for_' : '_files_for_';
                $row[$sys_col . $file->_table_field->field] = array_merge($row[$sys_col . $file->_table_field->field], [$file->getAttributes()]);
            }
        }

        //attach remotes
        $remotes = RemoteFile::where('table_id', '=', $table->id)
            ->whereIn('row_id', $allRows->pluck('id'))
            ->with('_table_field')
            ->get();

        foreach ($remotes as $remote) {
            $row = $allRows->where('id', '=', $remote->row_id)->first();
            if ($row && $remote->_table_field) {
                $remote->is_remote = 1;
                $sys_col = $remote->is_img ? '_images_for_' : '_files_for_';
                $row[$sys_col . $remote->_table_field->field] = array_merge($row[$sys_col . $remote->_table_field->field], [$remote->getAttributes()]);
            }
        }
    }

    /**
     * Check favorite rows in result collection
     *
     * @param Collection $allRows
     * @param Table $table
     * @param $user_id - integer|null
     */
    protected function checkFavorites(Collection $allRows, Table $table, $user_id)
    {
        $favorites = FavoriteRow::where('user_id', '=', $user_id)
            ->where('table_id', '=', $table->id)
            ->whereIn('row_id', $allRows->pluck('id'))
            ->get();
        foreach ($allRows as $row) {
            $is_favorite = $favorites->where('row_id', '=', ($row->id ?? null))->first();
            $row->_is_favorite = $is_favorite ? 1 : 0;
        }
    }

    /**
     * Check favorite rows in result collection
     *
     * @param Collection $allRows
     * @param Table $table
     */
    protected function attachUsersAndUGroups(Collection $allRows, Table $table)
    {
        [$users, $groups] = $this->getUsersAndGroupsForAttach($allRows, $table);

        foreach ($allRows as $row) {
            /*$creator = $users->where('id', '=', ($row->created_by ?? null) )->first();
            if ($creator) {
                $row->_created_by = $creator->first_name . ' ' . $creator->last_name;
            }
            $modifier = $users->where('id', '=', ($row->modified_by ?? null) )->first();
            if ($modifier) {
                $row->_modified_by = $modifier->first_name . ' ' . $modifier->last_name;
            }*/

            //add user name if some field has type='User'
            foreach ($table->_fields as $fld) {
                if ($fld->f_type === 'User') {
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
     * @param Collection $allRows
     * @param Table $table
     * @return array
     */
    protected function getUsersAndGroupsForAttach(Collection $allRows, Table $table)
    {
        $users_ids = $allRows->pluck('created_by')->merge($allRows->pluck('modified_by'));

        //merge user ids if some field has type='User' (it is for system tables)
        foreach ($table->_fields as $fld) {
            if ($fld->f_type === 'User') {
                $arrs = MselectData::convert($allRows->pluck($fld->field)->toArray(), $fld->input_type);
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
        $users_ids = $users_ids->filter(function ($id) {
            return !!$id;
        })->unique();

        $group_ids = $users_ids->filter(function ($id) {
            return !is_numeric($id);
        })
            ->map(function ($id) {
                return substr($id, 1);
            });

        $users_ids = $users_ids->filter(function ($id) {
            return is_numeric($id);
        });

        $users = User::whereIn('id', $users_ids)
            ->select($this->service->onlyNames(false))
            ->get();

        $groups = UserGroup::whereIn('id', $group_ids)
            ->select(['id', 'name'])
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
     * @param Collection $allRows
     * @param Table $table
     * @param int|null $user_id
     */
    protected function attachRowGroups(Collection $allRows, Table $table, int $user_id = null)
    {
        $sql = new TableDataQuery($table, false, $user_id ?: 0);
        $id = $sql->getSqlFld();

        $row_ids = $allRows->pluck('id');

        //get row ids referred to RowGroups from $allRows
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
        foreach ($allRows as $row) {
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
     * @param Collection $allRows
     * @param Table $table
     * @param int|null $user_id
     */
    protected function attachCondFormats(Collection $allRows, Table $table, int $user_id = null)
    {
        //get all applied CondFormats
        $_cond_formats = $table->_cond_formats()
            ->get()
            ->toArray();

        //set applied RowGroups and CondFormats to each row
        foreach ($allRows as $row) {
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
     * @param Collection $allRows
     * @param Table $table
     */
    protected function attachRefCondsById(Collection $allRows, Table $table)
    {
        try {
            $fields = $table->_fields()
                ->whereIn('input_type', $this->service->select_input)
                ->whereNotNull('ddl_id')
                ->get();

            if ($fields->count()) {
                //apply for each field with DDL
                foreach ($fields as $fld) {
                    $row_ids = collect([]);
                    foreach ($allRows as $data_row) {
                        $row_val = $this->dataRowVal($data_row, $fld->field);
                        $row_ids = $row_ids->merge(MselConvert::getArr($row_val));
                    }
                    $row_ids = $row_ids->filter();

                    $ref_rows = collect([]);
                    if ($row_ids->count()) {
                        $ref_rows = $this->getReferencedRowsFromDdl($row_ids, $fld->ddl_id);
                    }

                    foreach ($allRows as $data_row) {
                        $this->addRCs($data_row, $fld->field, $ref_rows);
                        $data_row->_rc_attached = true;
                    }
                }
                //-------
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param $data_row
     * @param $field
     * @return mixed|null
     */
    protected function dataRowVal($data_row, $field)
    {
        if ($data_row instanceof Arrayable) {
            $array = $data_row->toArray();
        } elseif (is_array($data_row)) {
            $array = $data_row;
        } else {
            $array = (array)$data_row;
        }
        return $array[$field] ?? null;
    }

    /**
     * @param Collection $row_vals
     * @param int|null $ddl_id
     * @return Collection
     */
    public function getReferencedRowsFromDdl(Collection $row_vals, int $ddl_id = null)
    {
        $result = collect($row_vals)->map(function ($item) {
            return (object)[
                'init_val' => $item,
                'show_val' => $item,
                'img_vals' => [],
                'ref_bg_color' => '',
                'max_selections' => '',
            ];
        });

        $this->recursive_passed = [];
        return $this->recursiveDdlSearch($result, $ddl_id);
    }

    /**
     * @param Collection $row_vals
     * @param int|null $ddl_id
     * @param int $dep
     * @return Collection
     */
    protected function recursiveDdlSearch(Collection $row_vals, int $ddl_id = null, int $dep = 1)
    {
        if ($dep >= 5) {
            return $row_vals;
        }

        if ($ddl_id) {
            //DDL Items
            $ddl_items = $this->getDdlItems($ddl_id);
            foreach ($row_vals as $res) {
                $di = $ddl_items->where('option', '=', $res->show_val)->first();
                if ($di) {
                    $res->show_val = $di->show_option ?: $di->option;

                    if (!$res->img_vals) {
                        $res->img_vals = $di->image_path ? [$di->image_path] : $res->img_vals;
                    }
                    if (!$res->ref_bg_color) {
                        $res->ref_bg_color = $di->opt_color ?: $res->ref_bg_color;
                    }
                    if (!$res->max_selections) {
                        $res->max_selections = $di->max_selections ?: $res->max_selections;
                    }
                }
            }

            //DDL References
            $references = $this->getReferences($ddl_id);
            $fileRepo = new FileRepository();
            foreach ($references as $dref) {
                //prevent cyclic links
                if (in_array($dref->id, $this->recursive_passed)) {
                    continue;
                }
                $this->recursive_passed[] = $dref->id;

                $tar_field = $dref->_target_field ? $dref->_target_field->field : 'id';
                $show_field = $dref->_show_field ? '`' . $dref->_show_field->field . '`' : '""';
                $img_field = $dref->_image_field ? '`' . $dref->_image_field->field . '`' : '""';
                $clr_field = $dref->_color_field ? '`' . $dref->_color_field->field . '`' : '""';
                $max_selections_field = $dref->_max_selections_field ? '`' . $dref->_max_selections_field->field . '`' : '""';

                $r_table = $dref->_ref_condition->_ref_table;
                $rSql = (new TableDataQuery($r_table))
                    ->getQuery()
                    ->distinct()
                    ->whereIn($tar_field, $row_vals->pluck('show_val')->unique());

                $refHasId = $rSql->count('id') < 1000;
                $id_field = $refHasId ? '`id`' : '""';

                $reffs = $rSql
                    ->selectRaw('' . $id_field . ' as `id`, `' . $tar_field . '` as `init_val`, ' . $show_field . ' as `show_val`, ' . $clr_field . ' as `ref_bg_color`, ' . $max_selections_field . ' as `max_selections`')
                    ->get();

                $ref_files = $dref->_image_field
                    ? $fileRepo->getPathsForRows($r_table->id, $dref->_image_field->id, $refHasId ? $reffs->pluck('id')->toArray() : [])
                    : collect([]);

                $def_clr_img = $dref->search_in_ref_colors()->where('ref_value', '=', 'Default:')->first();
                foreach ($row_vals as $res) {
                    $find = $reffs->where('init_val', '=', $res->show_val)->first();
                    if ($find) {
                        $res->show_val = $find->show_val ?: $find->init_val;
                        $ref_clr_img = $dref->search_in_ref_colors()->where('ref_value', '=', $res->show_val)->first() ?: $def_clr_img;

                        $showfiles = $ref_files->where('row_id', '=', $find->id);
                        $res->img_vals = $dref->has_individ_images
                            ? ($ref_clr_img && $ref_clr_img->image_ref_path ? [$ref_clr_img->image_ref_path] : [])
                            : ($showfiles->count() ? $showfiles->pluck('fullpath') : []);

                        $res->ref_bg_color = $dref->has_individ_colors
                            ? ($ref_clr_img && $ref_clr_img->color ? $ref_clr_img->color : '')
                            : ($find->ref_bg_color);

                        $res->max_selections = $dref->has_individ_max_selections
                            ? ($ref_clr_img && $ref_clr_img->max_selections ? $ref_clr_img->max_selections : '')
                            : ($find->max_selections);
                    }
                }

                if ($dref->_show_field) {
                    $row_vals = $this->recursiveDdlSearch($row_vals, $dref->_show_field->ddl_id, $dep + 1);
                }
            }
        }

        return $row_vals;
    }

    /**
     * @param int $ddl_id
     * @return Collection
     */
    protected function getDdlItems(int $ddl_id)
    {
        return (new DDLRepository())->ddlItemsWhichIds([$ddl_id]);
    }

    /**
     * @param int $ddl_id
     * @return Collection
     */
    protected function getReferences(int $ddl_id)
    {
        return (new DDLRepository())->ddlReferencesWhichIds([$ddl_id]);
    }

    /**
     * @param object $data_row
     * @param string $field
     * @param Collection $ref_rows
     */
    protected function addRCs(object $data_row, string $field, Collection $ref_rows)
    {
        $rcs = ['_is_ddlid' => ['show_val' => '']];
        $row_val = $this->dataRowVal($data_row, $field);
        foreach (MselConvert::getArr($row_val) as $row_item) {
            $row_item = is_numeric($row_item) ? floatval($row_item) : (string)$row_item;
            $rcs[$row_item] = $ref_rows->where('init_val', '=', $row_item)->first();
        }
        $data_row->{'_rc_' . $field} = array_filter($rcs);
    }

    /**
     * @param int $table_id
     * @param array $row_ids
     * @param array $select
     * @return Collection
     */
    public function getRowsById(int $table_id, array $row_ids, array $select = []): Collection
    {
        $table = (new TableRepository())->getTable($table_id);
        $sql = (new TableDataQuery($table))->getQuery();
        $sql->whereIn('id', $row_ids);
        if ($select) {
            $sql->select($select);
        }
        return $sql->get();
    }

    /**
     * Additionals for Vue.js
     *
     * @param Collection $rowsData
     */
    protected function attachAdditionals(Collection $rowsData)
    {
        foreach ($rowsData as $row) {
            $row->_checked_row = false;
        }
    }

    /**
     * Attach data for CorrespTables
     *
     * @param Collection $allRows
     * @param Table $table
     */
    protected function attachCorresps(Collection $allRows, Table $table)
    {
        if ($table->db_name == 'correspondence_fields') {
            $tb_names = Table::whereIn('db_name', $allRows->pluck('_table')->pluck('data_table'))
                ->select(['id', 'db_name', 'name'])
                ->get();
            $fld_names = TableField::whereIn('field', $allRows->pluck('data_field'))
                ->whereIn('table_id', $tb_names->pluck('id'))
                ->select(['id', 'field', 'name', 'table_id'])
                ->get();
            $link_tbs = Table::whereIn('db_name', $allRows->pluck('link_table_db'))
                ->select(['id', 'db_name', 'name'])
                ->get();
            $link_flds = TableField::whereIn('field', $allRows->pluck('link_field_db'))
                ->whereIn('table_id', $link_tbs->pluck('id'))
                ->select(['id', 'field', 'name', 'table_id'])
                ->get();
            foreach ($allRows as $row) {
                $datatable = $row->_table ? $row->_table->data_table : '';
                $tb = $tb_names->where('db_name', '=', $datatable)->first();
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
            $tb_names = Table::whereIn('db_name', $allRows->pluck('data_table'))
                ->select(['id', 'name', 'db_name'])
                ->get();
            foreach ($allRows as $row) {
                $tb = $tb_names->where('db_name', '=', $row->data_table)->first();
                $row->__data_table = $tb ? $tb->name : '';
            }
        }
    }

    /**
     * @param Table $table
     * @param DcrLinkedTable $dcrLinkedTable
     * @param array $parent_row_dcr
     * @return Collection
     */
    public function getDcrLinkedRows(Table $table, DcrLinkedTable $dcrLinkedTable, array $parent_row_dcr): Collection
    {
        $request_id = HelperService::dcr_id_linked_row($dcrLinkedTable->table_request_id, $parent_row_dcr['id'] ?? null);

        $tdq = new TableDataQuery($table);
        $tdq->applyRefConditionRow($dcrLinkedTable->_passed_ref_cond, $parent_row_dcr);
        $rows = $tdq->getQuery()->get();

        $rows = $rows->merge(
            (new TableDataQuery($table))
            ->getQuery()
            ->where('request_id', '=', $request_id)
            ->get()
        );

        $this->attachSpecialFields($rows, $table);

        return $rows;
    }

    /**
     * @param Table $table
     * @param DcrLinkedTable $dcrLinkedTable
     * @param array $filters
     * @return Collection
     */
    public function getDcrCatalog(Table $table, DcrLinkedTable $dcrLinkedTable, array $filters): Collection
    {
        $tdq = new TableDataQuery($table);
        $tdq->checkAndApplyDataRange($dcrLinkedTable->ctlg_data_range);
        $tdq->externalFilters($filters);
        $query = $tdq->getQuery();

        $row_ids = (clone $query)
            ->distinct()
            ->limit(100)
            ->groupBy($dcrLinkedTable->_ctlg_distinct_field->field)
            ->selectRaw('GROUP_CONCAT(id) as fid')
            ->get()
            ->pluck('fid')
            ->map(function ($item) {
                return Arr::first(explode(',', $item) ?: []);
            });

        $rows = (clone $query)
            ->whereIn('id', $row_ids)
            ->get();

        $this->attachSpecialFields($rows, $table);

        return $rows;
    }

    /**
     * @param Table $table
     * @param string $eriKeyPart
     * @return Collection
     */
    public function getEriRows(Table $table, string $eriKeyPart): Collection
    {
        return (new TableDataQuery($table))
            ->getQuery()
            ->where('request_id', 'like', "$eriKeyPart%")
            ->get()
            ->sort(function ($a, $b) {
                $aVal = explode('_', $a['request_id'] ?? '');
                $aVal = Arr::last($aVal);

                $bVal = explode('_', $b['request_id'] ?? '');
                $bVal = Arr::last($bVal);

                return $aVal <=> $bVal;
            })
            ->values();
    }

    /**
     * @param Table $table
     * @param string $static_hash
     * @return Model|null
     */
    public function getRowSRV(Table $table, string $static_hash)
    {
        $fld = $table->_srv_url ? $table->_srv_url->field : 'static_hash';
        return (new TableDataQuery($table))
            ->getQuery()
            ->where($fld, '=', $static_hash)
            ->first();
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
                    $ref_rows = $this->getReferencedRowsFromDdl(collect([$fld->unit]), $fld->unit_ddl_id);
                    $this->addRCs($fld, 'unit', $ref_rows);
                }
                if ($fld->unit_ddl_id && $fld->unit_display) {
                    $ref_rows = $this->getReferencedRowsFromDdl(collect([$fld->unit_display]), $fld->unit_ddl_id);
                    $this->addRCs($fld, 'unit_display', $ref_rows);
                }
            }
        } catch (Exception $e) {
        }
        //-------
    }

    /**
     * @param Table $table
     * @param string $fld
     * @param string|null $value
     * @return Collection
     */
    public function getRowIdsForField(Table $table, string $fld, string $value = null): Collection
    {
        return (new TableDataQuery($table, false, $table->user_id))
            ->getQuery()
            ->where($fld, '=', $value)
            ->select('id')
            ->get()
            ->pluck('id');
    }

    /**
     * @param Table $table
     * @param int|null $row_group_id
     * @return Builder
     */
    public function dataQuerySql(Table $table, int $row_group_id = null): Builder
    {
        $sql = new TableDataQuery($table);
        if ($row_group_id) {
            $sql->applySelectedRowGroup($row_group_id);
        }
        return $sql->getQuery();
    }

    /**
     * @param Table $table
     * @param array $request_params
     * @param int|null $user_id
     * @param array $moreFilters
     * @return array
     * @throws Exception
     */
    public function getOnlyRows(Table $table, array $request_params, int $user_id = null, array $moreFilters = [])
    {
        $sql = new TableDataQuery($table, true, auth()->id());
        //apply 'row group' if selected
        if (!empty($moreFilters['row_group_id'])) {
            $sql->applySelectedRowGroup($moreFilters['row_group_id']);
        }
        //apply 'saved filters' if selected
        if (!empty($moreFilters['saved_filter_id'])) {
            $sf = (new TableSavedFilterRepository())->get($moreFilters['saved_filter_id']);
            $sql->externalFilters($sf->filters_object);
        }
        //apply searching, filtering, row rules to getRows
        $sql->testViewAndApplyWhereClauses($request_params, auth()->id());
        $sql->applySorting($request_params['sort'] ?? []);
        //limit is 200rows
        if ($sql->getQuery()->count() > 200) {
            throw new Exception('Limit is 200 rows!', 1);
        }
        //attach DDL, files, etc.
        $rowsData = $sql->getQuery()->get();
        $this->attachSpecialFields($rowsData, $table, $user_id);
        return [
            'rows' => $rowsData,
            'filters' => $sql->filters,
        ];
    }

    /**
     * @param Table $table
     * @param array $clauses
     * @param int $offset
     * @param int $limit
     * @return Collection
     */
    public function listRows(Table $table, array $clauses, int $offset, int $limit = 100): Collection
    {
        $sql = new TableDataQuery($table, true, auth()->id());
        $sql = $sql->getQuery(false);
        if (!empty($clauses['row_id'])) {
            $sql->where('id', '=', $clauses['row_id']);
        }
        $rowsData = $sql->offset($offset)
            ->limit($limit)
            ->get();
        //attach 'refs'.
        $this->attachSpecialFields($rowsData, $table, $table->user_id, ['refs']);
        return $rowsData;
    }
}