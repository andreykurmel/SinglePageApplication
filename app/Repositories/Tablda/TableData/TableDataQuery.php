<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\Plan;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserSubscription;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableDataQuery
{
    public $filters = [];
    public $row_group_statuses = [];
    public $groups_hidden_row_ids = [];
    public $table_permission_id = null;

    /**
     * @var HelperService
     */
    protected $service;
    /**
     * @var Builder
     */
    protected $query;
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var array
     */
    protected $user_fields;

    protected $no_join = false;
    protected $isAppliedSysRules = false;

    /**
     * TableDataQuery constructor.
     *
     * @param Table $table
     * @param bool $no_join
     * @param int|null $prepare_relations_user
     */
    public function __construct(Table $table, bool $no_join = false, int $prepare_relations_user = null)
    {
        $this->user_fields = (new TableDataRepository())->tableUserFields($table);

        $this->no_join = $no_join;
        $this->table = $table;
        if (!is_null($prepare_relations_user) && !$table->_t_prepared_rels) {
            $this->prepareRelations($prepare_relations_user);
            $table->_t_prepared_rels = true;
        }

        $this->query = $this->getTableDataSql($table, $no_join);

        $this->service = new HelperService();
    }

    /**
     * Load all relations used in this module.
     *
     * @param int|null $user_id
     */
    protected function prepareRelations(int $user_id = null)
    {
        $table = $this->table;
        $this->table->load([
            '_fields' => function ($q) use ($user_id, $table) {
                $q->joinHeader($user_id, $table);
            },
            '_row_groups' => function ($q) {
                $q->with([
                    '_ref_condition' => function ($q) {
                        $q->with((new TableRefConditionRepository())->refConditionRelations(true));
                    },
                    '_regulars'
                ]);
            },
        ]);
    }

    /**
     * Get sql Builder for TableData.
     *
     * @param \Vanguard\Models\Table\Table $table
     * @param bool $no_join
     * @return Builder
     */
    protected function getTableDataSql(Table $table, bool $no_join = false)
    {
        if ($table->source == 'remote') {
            $conn = $this->service->configRemoteConnection($table->connection_id);
        }

        if ($table->db_name === 'sum_usages') {
            $sql = Table::query();
            if (!$no_join) {
                $sql->join('users', 'users.id', '=', 'tables.user_id')
                    ->select('users.*', 'tables.*', 'tables.id as tb_id');
            }
        } elseif ($table->db_name === 'fees') {
            $sql = Plan::query();
        } elseif ($table->db_name === 'table_fields__for_tooltips') {
            $sql = TableField::query();
            if (!$no_join) {
                $sql->join('tables', 'table_fields.table_id', '=', 'tables.id')
                    ->select('table_fields.*', 'tables.name as tb_name');
            }
        } elseif ($table->db_name === 'user_subscriptions') {
            $sql = UserSubscription::query();
            if (!$no_join) {
                $sql->join('users', 'users.id', '=', 'user_subscriptions.user_id')
                    ->select('user_subscriptions.*', 'users.renew', 'users.recurrent_pay', 'users.avail_credit');
            }
        } elseif ($table->db_name === 'correspondence_tables') {
            $sql = CorrespTable::query();
            if (!$no_join) {
                //for correct filters by columns 'App,Name' , 'App,Array'. See -> HelperService::convertSysField
                $sql->join('correspondence_apps', 'correspondence_tables.correspondence_app_id', '=', 'correspondence_apps.id')
                    ->select('correspondence_tables.*');
            }
        } elseif ($table->db_name === 'correspondence_fields') {
            $sql = CorrespField::query();
            if (!$no_join) {
                //for correct filters by columns 'App,Name' , 'App,Array'. See -> HelperService::convertSysField
                $sql->join('correspondence_apps', 'correspondence_fields.correspondence_app_id', '=', 'correspondence_apps.id')
                    ->join('correspondence_tables', 'correspondence_fields.correspondence_table_id', '=', 'correspondence_tables.id')
                    ->select('correspondence_fields.*');
            }
        } else {
            $sql = new TableData();
            $sql = $sql->setTable(isset($conn) ? $conn['table'] : $table->db_name)
                ->setConnection($this->getConn($table))
                ->newQuery();
        }
        return $sql;
    }

    /**
     * Get Table's DataBase.
     *
     * @param $table
     * @return string
     */
    public function getConn($table)
    {
        if ($table->source == 'remote') {
            return 'mysql_remote';
        } elseif ($table->is_system == 2) {
            return 'mysql_correspondence';
        } elseif ($table->is_system == 1) {
            return 'mysql';
        } else {
            return 'mysql_data';
        }
    }

    /**
     * @param TableRefCondition $ref_cond
     * @return int
     */
    public function hasReferencesInFormulas(TableRefCondition $ref_cond)
    {
        $formula_fields = $this->table->_fields->where('input_type', '=', 'Formula');
        foreach ($formula_fields as $fld) {
            $this->query->orWhere($fld->field . '_formula', 'like', '%"' . $ref_cond->name . '"%');
        }
        return $this->query->count();
    }

    /**
     * @param bool $applySysRules
     * @return Builder
     */
    public function getQuery(bool $applySysRules = true)
    {
        if ($applySysRules) {
            $this->applySystemRules();
        }
        return $this->query;
    }

    /**
     * clearQuery
     */
    public function clearQuery()
    {
        $this->query = $this->getTableDataSql($this->table, $this->no_join);
    }

    /**
     * @param $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Apply special select rules for System Tables.
     */
    protected function applySystemRules()
    {
        if ($this->isAppliedSysRules) {
            return;
        }

        if ($this->table->is_system) {
            if ($this->table->db_name == 'table_fields__for_tooltips') {
                $sys_table_ids = Table::where('is_system', '>', 0)->get(['id'])->pluck('id');
                $this->query->whereIn('table_fields.table_id', $sys_table_ids);
            }

            //plans and subscriptions
            if ($this->table->db_name == 'plan_features') {
                $this->query->where('type', '=', 'user');
                $this->query->whereRaw('EXISTS (SELECT id FROM users WHERE id = `object_id`)');
            }
            if ($this->table->db_name == 'sum_usages') {
                $this->query->where('is_system', '=', '0');
                $this->query->with('_connection');
            }
            if ($this->table->db_name == 'user_subscriptions') {
                $this->query->where('active', '=', '1');
                $this->query->with(['_addons', '_plan']);
            }

            //correspondences
            if ($this->table->db_name == 'correspondence_apps') {
                $this->query->where('is_active', 1);
            }
            if ($this->table->db_name == 'correspondence_tables') {
                $this->query->whereHas('_app', function ($q) {
                    $q->where('is_active', 1);
                });
                $this->query->with(['_app']);
            }
            if ($this->table->db_name == 'correspondence_fields') {
                $this->query->whereHas('_app', function ($q) {
                    $q->where('is_active', 1);
                });
                $this->query->with(['_app', '_table']);
            }
        }

        if ($this->table->is_system != 1) {
            $r_h = $this->getSqlFld('row_hash');
            $this->query->where(function ($q) use ($r_h) {
                $q->whereNotIn($r_h, (new HelperService())->sys_row_hash);
                $q->orWhereNull($r_h); //Because only 'Where Not In' cannot get records with NULL
            });
        }

        $this->isAppliedSysRules = true;
    }

    /**
     * @param string $field
     * @param Table|null $table
     * @return string
     */
    public function getSqlFld(string $field = 'id', Table $table = null)
    {
        return SqlFieldHelper::getSqlFld($table ?: $this->table, $field);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Apply ref conditions to SQL.
     *
     * @param TableRefCondition $ref_condition
     * @param array $row
     * @param bool $apply_permis
     */
    public function applyRefConditionRow(TableRefCondition $ref_condition, array $row = [], $apply_permis = true)
    {
        $this->applyRefConditionToQuery($this->query, $ref_condition, $row ?: null);
        if ($apply_permis) {
            $this->applyRowRightsForUser(auth()->id());
        }
    }

    /**
     * @param Builder $cond_query
     * @param TableRefCondition $ref_condition
     * @param array|null $present_row
     */
    protected function applyRefConditionToQuery(Builder $cond_query, TableRefCondition $ref_condition, array $present_row = null)
    {
        $cond_query = (new RefConditionApplier($ref_condition))->applyToQuery($cond_query, $present_row);
    }

    /**
     * If user isn`t 'admin' then they can see only part of table rows.
     *
     * @param $user_id - integer|null
     * @param $special_params
     */
    protected function applyRowRightsForUser($user_id, array $special_params = [])
    {
        //if Owner is editing the 'View' -> apply permission rows
        if (!empty($special_params['edited_view_hash'])) {
            $special_params['view_object'] = (new TableViewRepository())->getByHash($special_params['edited_view_hash']);
        }

        if (!empty($special_params['view_object'])) {
            $this->table_permission_id = ($special_params['view_object'])->access_permission_id;//Only for VIEW module
        } else {
            $this->table_permission_id = $special_params['table_permission_id'] ?? null;//Only for DCR module
        }

        if ($this->table->user_id != $user_id || $this->table_permission_id) {
            $row_group_edit = $special_params['row_group_edit'] ?? null;
            $row_groups = $this->getAvailableRowGroups($this->table_permission_id, $row_group_edit);
            $row_group_edit_apply = $special_params['row_group_edit_apply'] ?? null;
            if ($row_group_edit_apply && !$row_groups->count()) {
                //if NONE edit RowGroups
                $this->query->whereRaw('false');
            }

            $this->applyRowGroupsToQuery($this->query, $row_groups);

            $this->forSystemTableUserCanGetSpecialRows($user_id);
        }
    }

    /**
     * Get Available Row Groups.
     *
     * @param null $table_permission_id
     * @param null $row_group_edit
     * @return mixed
     */
    protected function getAvailableRowGroups($table_permission_id = null, $row_group_edit = null)
    {
        $row_groups = (new TableRowGroupRepository())->getAvailableRowGroups($this->table->id, $table_permission_id, $row_group_edit);
        $row_groups->load([
            '_ref_condition' => function ($q) {
                $q->with('_items');
            },
            '_regulars'
        ]);
        return $row_groups;
    }

    /**
     * Apply RowGroups to the Query.
     *
     * @param Builder $query
     * @param EloquentCollection $row_groups
     * @param bool $and
     */
    protected function applyRowGroupsToQuery(Builder $query, EloquentCollection $row_groups, bool $and = false)
    {
        if ($row_groups && count($row_groups)) {

            $row_groups->loadMissing([
                '_ref_condition' => function ($q) {
                    $q->with((new TableRefConditionRepository())->refConditionRelations(true));
                },
                '_regulars'
            ]);

            $query->where(function ($right_q) use ($row_groups, $and) {

                foreach ($row_groups as $r_group) {
                    $method = $and ? 'where' : 'orWhere';
                    $right_q->{$method}(function ($cond_query) use ($r_group) {

                        //Referencing Conditions
                        if ($r_group->_ref_condition && $r_group->_ref_condition->ref_table_id) {
                            $this->applyRefConditionToQuery($cond_query, $r_group->_ref_condition);
                        }
                        //Regular Items
                        if ($r_group->_regulars) {
                            $allowed_vals = $r_group->_regulars->pluck('field_value')->toArray();
                            if ($allowed_vals) {
                                $cond_query->orWhereIn($this->getSqlFld('id'), $allowed_vals);
                            }
                        }

                    });
                }

            });
        }
    }

    /**
     * @param $user_id
     */
    protected function forSystemTableUserCanGetSpecialRows($user_id)
    {
        //filter rows for not owner if some field has type='User' (it is for system tables)
        if ($this->table->is_system) {
            foreach ($this->table->_fields as $fld) {
                if (in_array($fld->field, ['user_id', 'object_id']) && $fld->f_type === 'User') {
                    $this->query->where($this->getSqlFld($fld->field), '=', $user_id);
                }
            }
        }
    }

    /**
     * Apply View's clauses and Where clauses.
     * Also can return 'filters' and 'row group statuses'.
     *
     * @param array $data
     * @param int|null $user_id
     */
    public function testViewAndApplyWhereClauses(array $data, int $user_id = null)
    {
        $special_params = $data['special_params'] ?? [];

        //loaded from Folder View
        if (!empty($special_params['is_folder_view'])) {
            $special_params['view_hash'] = (new FolderRepository())->getAssignedView($special_params['is_folder_view'], $data['table_id']);
        }
        //load selected TableView
        $special_params['view_object'] = (new TableViewRepository())->getByHash($special_params['view_hash'] ?? null);


        //if User are seeing the 'View' -> always apply View's where clause.
        if (!empty($special_params['view_object'])) {
            if ($special_params['view_object']->row_group_id) {
                $this->applySelectedRowGroup($special_params['view_object']->row_group_id);
            }
            //TODO: saved view data mark
//            $view_data = json_decode($special_params['view_object']->data, 1);
//            $view_data = $this->cloneNeededData($view_data, $data);
//            $view_data['row_id'] = $data['row_id'] ?? null;
        }

        //apply map searching, filtering, row rules to getRows
        //get Filters and RowGroupStatuses
        $this->applyWhereClause($data, $user_id, $special_params);
    }

    /**
     * @param array $view_data
     * @param array $data
     * @return array
     */
    protected function cloneNeededData(array $view_data, array $data)
    {
        if (empty($view_data['special_params'])) {
            $view_data['special_params'] = $data['special_params'] ?? [];
        } else {
            $view_data['for_list_view'] = $data['special_params']['for_list_view'] ?? false;
        }

        $view_data['applied_filters'] = $data['applied_filters'] ?? [];

        $view_data['temp_filters'] = $data['temp_filters'] ?? [];

        return $view_data;
    }

    /**
     * Apply searching, filtering, row rules to Query.
     * This function is used for TableDatas.
     *
     * @param array $data
     * @param $user_id
     * @param array $special_params
     */
    public function applyWhereClause(array $data, $user_id, array $special_params = [])
    {
        $user_id = $this->service->forceGuestForPublic($user_id);

        $tb = $this->table;
        $this->table->loadMissing([
            '_fields' => function ($q) use ($user_id, $tb) {
                $q->joinHeader($user_id, $tb);
            }
        ]);

        //If user isn`t 'admin' then they can see only part of table rows.
        if (empty($data['force_execute'])) {
            $this->applyRowRightsForUser($user_id, $special_params);
        }

        //Apply Searching before filters for FilterUpdating on Searching
        $this->applySearch($data);

        //Get rows ids hidden by RowGroups
        $this->groups_hidden_row_ids = $this->ifToggledRowGroup($data['hidden_row_ids'] ?? [], $data);


        //GET FILTERS VALUES BEFORE OTHER 'WHERES'
        if (!empty($special_params['for_list_view'])) {
            $this->filters = (new TableDataFiltersModule($this))->getFilters($data);
        }

        //GET ROW GROUPS STATUSES BEFORE OTHER 'WHERES'
        if (!empty($special_params['for_list_view'])) {
            $this->row_group_statuses = $this->getRowGroupStatuses($data);
        }


        //Initial view for Table is 'Blank'
        if (!empty($data['first_init_view']) && $data['first_init_view'] == -2) {
            $this->query->whereRaw('false');
        }

        //Get only one (or selected) row(s)
        if (!empty($data['row_id'])) {
            if (is_array($data['row_id'])) {
                $this->query->whereIn($this->getSqlFld('id'), $data['row_id']);
            } else {
                $this->query->where($this->getSqlFld('id'), $data['row_id']);
            }
        }

        //Apply special 'Wheres' from other modules.
        $this->applyNotListViewWheres($data, $user_id, $special_params);

        //Apply special select rules for System Tables.
        $this->applySystemRules();

        //Apply filters to the query
        $this->applyFilters($this->query, $data);

        //Apply rows hidden by RowGroups
        $this->applyHiddenRowIds($this->query, $this->groups_hidden_row_ids);

        if (!empty($data['only_favorites'])) {
            $this->getOnlyFavoriteRows($user_id);
        }
    }

    /**
     * Apply search rules to the query
     * $search_words can be array of items ['word'=>'', 'type'=>''] OR string with 'word1 AND word2'
     *
     * @param array $data
     * @param bool $can_empty
     */
    public function applySearch(array $data, bool $can_empty = false)
    {
        //apply searching from 'ListView'
        if (!empty($data['search_words']) && !empty($data['search_columns'])) {

            $this->query->where(function ($search_q) use ($can_empty, $data) {

                $data['search_columns'] = $this->expandSearchUserColumns($data['search_columns']);

                if (!is_array($data['search_words'])) {
                    $data['search_words'] = $this->searchStringToArr($data['search_words']);
                }

                foreach ($data['search_words'] as $word_obj) {

                    $word = (string)($word_obj['word'] ?? '');
                    $func = ($word_obj['type'] ?? null) == 'or' ? 'orWhere' : 'where';
                    $direct_fld = $word_obj['direct_fld'] ?? null;

                    if ($can_empty || $word) {
                        $search_q->{$func}(function ($q) use ($word, $data, $direct_fld) {
                            if ($direct_fld) {
                                $this->applySearchWord($q, $direct_fld, $word);
                            } else {
                                foreach ($data['search_columns'] as $field) {
                                    $this->applySearchWord($q, $field, $word);
                                }
                            }
                        });
                    }

                }
            });
        }

        //get rows which are found by 'radius search'
        if (!empty($data['radius_search'])) {
            $distance_str = '6371*acos(';
            $distance_str .= ' ( ';
            $distance_str .= 'cos(' . $data['radius_search']['center_lat'] . '*PI()/180)';//Lat_A
            $distance_str .= '*';
            $distance_str .= 'cos(' . $data['radius_search']['field_lat'] . '*PI()/180)';//Lat_B
            $distance_str .= '*';
            $distance_str .= 'cos(' . $data['radius_search']['field_long'] . '*PI()/180 - ' . $data['radius_search']['center_long'] . '*PI()/180)';//Long_B - Long_A
            $distance_str .= ' ) ';
            $distance_str .= '+';
            $distance_str .= ' ( ';
            $distance_str .= 'sin(' . $data['radius_search']['center_lat'] . '*PI()/180)';//Lat_A
            $distance_str .= '*';
            $distance_str .= 'sin(' . $data['radius_search']['field_lat'] . '*PI()/180)';//Lat_B
            $distance_str .= ' ) ';
            $distance_str .= ')';
            $this->query->whereRaw(DB::raw($distance_str . ' < ' . $data['radius_search']['km']));
        }

        //Apply TableView filtering
        if (!empty($data['search_view'])) {
            $this->query->where(function ($search_q) use ($data) {
                foreach ($data['search_view'] as $viewfilt) {
                    switch ($viewfilt['criteria']) {
                        case 'contain': $search_q->where($viewfilt['field'], 'like', '%'.$viewfilt['search'].'%');
                        case 'less': $search_q->where($viewfilt['field'], '<', $viewfilt['search']);
                        case 'more': $search_q->where($viewfilt['field'], '>', $viewfilt['search']);
                        case 'equal':
                        case 'match':
                        default: $search_q->where($viewfilt['field'], '=', $viewfilt['search']);
                    }
                }
            });
        }
    }

    /**
     * @param Builder $q
     * @param string $db_field
     * @param string|null $word
     */
    protected function applySearchWord(Builder $q, string $db_field, string $word = null)
    {
        $arr = explode('.', $db_field);
        $just_field = array_pop($arr);
        $header = $this->table
            ->_fields
            ->where('field', $just_field)
            ->first();

        $operator = '=';

        if ($word) {
            $word = trim($word);
            if ($this->findStrInBracers($word)) {
                $bracers = true;
                $word = $this->removeBracers($word);
            } else {
                $word = $this->changeFindStrForUserColumns($db_field, $word);
            }

            if (empty($bracers)) {
                $operator = 'like';
                $word = '%'.$word.'%';
            }
        }

        if ($header) {
            $q->orWhere(function(Builder $in) use ($header, $db_field, $operator, $word) {
                (new FieldTypeApplier($header->f_type, $db_field))->where($in, $operator, $word);
            });
        }
    }

    /**
     * Expand search columns for System tables.
     *
     * @param array $search_columns
     * @return array
     */
    protected function expandSearchUserColumns(array $search_columns)
    {
        //remove sys columns and empty columns
        // && set table pointer
        foreach ($search_columns as $i => $col) {
            if (!$col || preg_match('/^[^a-zA-Z\d]/i', $col) || in_array($col, $this->service->system_fields)) {
                unset($search_columns[$i]);
            } else {
                $col = $this->service->convertSysField($this->table, $col);
                $search_columns[$i] = $this->getSqlFld($col);
            }
        }

        //for sys tables.
        if ($this->table->is_system == 1) {
            $col = null;
            foreach ($search_columns as $column) {
                if (preg_match('#\.object_id#i', $column)) {
                    $col = $column;
                } elseif (preg_match('#\.user_id#i', $column)) {
                    $col = $column;
                }
            }

            if ($col) {
                //for correct search working
                $this->query->join('users as u_search', 'u_search.id', '=', $col)
                    ->select($this->getSqlFld('*'));
                //expand search columns
                $search_columns = array_merge($search_columns, $this->service->onlyNames(false, true, 'u_search'));
            }
        }

        return $search_columns;
    }

    /**
     * @param string $search
     * @return array
     */
    public function searchStringToArr(string $search)
    {
        $search = preg_replace('/[&]/', 'AND', $search);
        $search = preg_replace('/[,\|]/', 'OR', $search);
        $arr = preg_split('/( AND | OR )/', $search, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];
        for ($idx = 0; $idx < count($arr); $idx += 2) {
            $result[] = [
                'word' => trim($arr[$idx]),
                'type' => trim(strtolower($arr[$idx - 1] ?? 'and'))
            ];
        }
        return $result;
    }

    /**
     * @param string $search
     * @return bool
     */
    protected function findStrInBracers(string $search)
    {
        return $search[0] == '"'
            && $search[strlen($search) - 1] == '"';
    }

    /**
     * @param string $search
     * @return bool
     */
    protected function removeBracers(string $search)
    {
        return substr($search, 1, strlen($search) - 2);
    }

    /**
     * @param string $fld
     * @param string $init
     * @return string
     */
    public function changeFindStrForUserColumns(string $fld, string $init)
    {
        $init = preg_replace('/\'/i', '', $init);
        if (!$init || $this->changeFindStringShouldNotApply($fld, $init)) {
            return $init;
        }

        $replace_arr = explode(' ', $init);
        $replace_arr = array_map(function ($item) {
            return strtolower(trim(preg_replace('#\(|\)#i', '', $item)));
        }, $replace_arr);

        //find UserGroup
        if (in_array('group', $replace_arr)) {
            $usergroup = UserGroup::where('user_id', auth()->id())
                ->where(function ($q) use ($replace_arr) {
                    foreach ($replace_arr as $item) {
                        $q->orWhere('name', $item);
                    }
                })
                ->first();
            $replaced = $usergroup ? '_' . $usergroup->id : 'false';
        } //find User
        else {
            $user = User::whereHas('_member_of_groups', function ($q) {
                    $q->where('user_groups.user_id', auth()->id());
                })
                ->where(function ($q) use ($replace_arr) {
                    foreach ($replace_arr as $item) {
                        $q->orWhere('first_name', $item);
                        $q->orWhere('last_name', $item);
                    }
                })
                ->first();
            $replaced = $user ? $user->id : 'false';
        }

        return $replaced;
    }

    /**
     * @param string $fld
     * @param string $init
     * @return bool
     */
    protected function changeFindStringShouldNotApply(string $fld, string $init)
    {
        $fld_arr = explode('.', $fld);
        $fld = array_pop($fld_arr);

        $is_user_fld = in_array($fld, $this->user_fields);
        $is_number = is_numeric($init) || is_numeric(substr($init, 1));
        $is_mselect = strlen($init) > 0 && $init[0] == '[';

        return !$is_user_fld || $is_number || $is_mselect;
    }

    /**
     * If toggled RowGroup -> add or remove row_ids to hidden_row_ids.
     *
     * @param array $hidden_row_ids
     * @param array $data
     * @return array
     */
    protected function ifToggledRowGroup(array $hidden_row_ids, array $data)
    {
        $this->table->loadMissing('_row_groups');

        if (!empty($data['row_group_toggled'])) {
            if ($data['row_group_toggled']['id'] == 'all') {
                $hidden_row_ids = $this->allRowGroupsToggle($data);
            } else {
                $hidden_row_ids = $this->singleRowGroupToggle($hidden_row_ids, $data);
            }
        }
        return $hidden_row_ids;
    }

    /**
     * All row groups toggle
     *
     * @param array $data
     * @return array
     */
    protected function allRowGroupsToggle(array $data)
    {
        return !$data['row_group_toggled']['should_enable']
            ?
            $this->getRowGroupsIds(new EloquentCollection())//get IDs of all rows
            :
            [];
    }

    /**
     * getRowGroupsIds.
     *
     * @param EloquentCollection $row_groups
     * @return array
     */
    protected function getRowGroupsIds(EloquentCollection $row_groups)
    {
        $sql = $this->getTableDataSql($this->table);
        $this->applyRowGroupsToQuery($sql, $row_groups);

        return $sql->select($this->getSqlFld('id'))
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Single row group toggle
     *
     * @param array $hidden_row_ids
     * @param array $data
     * @return array
     */
    protected function singleRowGroupToggle(array $hidden_row_ids, array $data)
    {
        $row_group = $this->table
            ->_row_groups
            ->where('id', $data['row_group_toggled']['id']);

        $row_group_ids = $this->getRowGroupsIds($row_group);

        if ($data['row_group_toggled']['should_enable']) {
            //remove rows from hidden
            $hidden_row_ids = array_diff($hidden_row_ids, $row_group_ids);
        } else {
            //add rows to hidden
            $hidden_row_ids = array_merge($hidden_row_ids, $row_group_ids);
        }
        return array_unique($hidden_row_ids);
    }

    /**
     * Get Indeterminated RowGroups.
     *
     * @param array $data
     * @return array
     */
    protected function getRowGroupStatuses(array $data)
    {
        $this->table->loadMissing('_row_groups');
        $this->table->_row_groups->loadMissing('_ref_condition', '_regulars');

        $hidden_query = clone $this->query;
        $this->applyHiddenRowIds($hidden_query, $this->groups_hidden_row_ids);

        $filter_query = clone $this->query;
        $this->applyFilters($filter_query, $data);

        $statuses = [];
        foreach ($this->table->_row_groups as $row_group) {
            $show_status = 2;
            $filter_disabled = false;

            $total_ids = $this->getStatusIds($this->query, $row_group);

            if ($total_ids) {
                if (!empty($data['applied_filters'])) {
                    $filter_ids = $this->getStatusIds($filter_query, $row_group);
                    $filter_disabled = $filter_ids == 0;
                    $show_status = $filter_ids == 0 ? 0 : ($total_ids > $filter_ids ? 1 : 2);
                }

                if (!$filter_disabled && count($this->groups_hidden_row_ids)) {
                    $showed_ids = $this->getStatusIds($hidden_query, $row_group);
                    $show_status = $showed_ids == 0 ? 0 : ($total_ids > $showed_ids ? 1 : 2);
                }
            }

            $statuses[] = [
                'id' => $row_group->id,
                'show_status' => $show_status,
                'search_hidden' => !$total_ids,
                'filter_disabled' => $filter_disabled,
            ];
        }
        return $statuses;
    }

    /**
     * Apply HiddenRowGroups.
     *
     * @param Builder $query
     * @param array $hidden_row_ids
     */
    public function applyHiddenRowIds(Builder $query, array $hidden_row_ids)
    {
        if (count($hidden_row_ids)) {
            $query->whereNotIn($this->getSqlFld('id'), $hidden_row_ids);
        }
    }

    /**
     * @param array $filters
     */
    public function externalFilters(array $filters)
    {
        $this->applyFilters($this->query, ['applied_filters' => $filters]);
    }

    /**
     * Apply filters to the query
     *
     * @param Builder $query
     * @param array $data
     */
    protected function applyFilters(Builder $query, array $data)
    {
        $applied_filters = !empty($data['applied_filters'])
            ? (new TableDataFiltersModule($this))->prepareApplied($data['applied_filters'])
            : [];

        foreach ($applied_filters as $a_filter) {

            $filter_field = $this->service->convertSysField($this->table, $a_filter['field']);
            $filter_field = $this->getSqlFld($filter_field);

            if (($a_filter['filter_type'] ?? 'value') == 'value') {

                if (!empty($a_filter['input_type']) && MselectData::isMSEL($a_filter['input_type'])) {
                    $query->where(function ($inner) use ($filter_field, $a_filter) {
                        $inner->orWhereRaw('false'); // if all unchecked -> none of results
                        foreach ($a_filter['values'] as $item) {
                            $inner->orWhere($filter_field, 'like', '%' . $item['val'] . '%');
                        }
                    });
                } else {
                    $query->where(function ($inner) use ($filter_field, $a_filter) {
                        $a_filter_values = array_pluck($a_filter['values'], 'val');
                        (new FieldTypeApplier($a_filter['f_type'], $filter_field))
                            ->whereIn($inner, $a_filter_values, false);
                    });
                }

            }

            if (($a_filter['filter_type'] ?? 'value') == 'range') {
                $query->where(function ($q) use ($filter_field, $a_filter) {
                    $q->where($filter_field, '>=', $a_filter['values']['min']['selected']);
                    $q->where($filter_field, '<=', $a_filter['values']['max']['selected']);
                });
            }

        }
    }

    /**
     * @param Builder $query
     * @param TableRowGroup $row_group
     * @return int
     */
    protected function getStatusIds(Builder $query, TableRowGroup $row_group)
    {
        $row_group_query = clone $query;
        $this->applyRowGroupsToQuery($row_group_query, new EloquentCollection([$row_group]));
        return $row_group_query->count();
    }

    /**
     * Apply special 'Wheres' from other modules.
     *
     * @param array $data
     * @param $user_id
     * @param array $special_params
     */
    protected function applyNotListViewWheres(array $data, $user_id, array $special_params = [])
    {
        //get rows which are situated in map bounds (SQL must have select as `lat` and `lng`)!!!
        // ONLY FOR '/ajax/table-data/get-map-markers' path
        if (!empty($data['map_bounds']) && !empty($data['map_bounds']['left_bottom']) && !empty($data['map_bounds']['right_top'])) {
            $data['map_bounds'] = $this->fixBounds($data['map_bounds']);
            $lat_header = $this->table->_fields->where('is_lat_field', 1)->first();
            $long_header = $this->table->_fields->where('is_long_field', 1)->first();
            if ($lat_header && $long_header) {
                $this->query->where(function ($q) use ($data, $lat_header, $long_header) {
                    $q->where($lat_header->field, '>', $data['map_bounds']['left_bottom']['lat']);
                    $q->where($lat_header->field, '<', $data['map_bounds']['right_top']['lat']);
                    //AND
                    if ($data['map_bounds']['left_bottom']['lng'] < $data['map_bounds']['right_top']['lng']) {
                        $q->where($long_header->field, '>', $data['map_bounds']['left_bottom']['lng']);
                        $q->where($long_header->field, '<', $data['map_bounds']['right_top']['lng']);
                    } else {
                        $q->where(function ($sub) use ($long_header, $data) {
                            $sub->where($long_header->field, '>', $data['map_bounds']['left_bottom']['lng']);
                            $sub->orWhere($long_header->field, '<', $data['map_bounds']['right_top']['lng']);
                        });
                    }
                });
            }
        }

        // ONLY FOR 'DCR module'
        if (!empty($special_params['table_permission_id'])) {
            $permission = (new TablePermissionRepository())->getPermission($special_params['table_permission_id']);
            //get rows for selected table_data_request
            $this->query->where('request_id', $special_params['table_permission_id']);

            //select only those rows, which have createdBy == auth
            $this->query->where('created_by', $user_id);
        }
    }

    /**
     * @param array $map_bounds
     * @return array
     */
    protected function fixBounds(array $map_bounds)
    {
        if (is_null($map_bounds['left_bottom']['lat'])) {
            $map_bounds['left_bottom']['lat'] = PHP_INT_MIN;
        }
        if (is_null($map_bounds['left_bottom']['lng'])) {
            $map_bounds['left_bottom']['lng'] = PHP_INT_MIN;
        }
        if (is_null($map_bounds['right_top']['lat'])) {
            $map_bounds['right_top']['lat'] = PHP_INT_MAX;
        }
        if (is_null($map_bounds['right_top']['lng'])) {
            $map_bounds['right_top']['lng'] = PHP_INT_MAX;
        }
        return $map_bounds;
    }

    /**
     * Get only favorite rows for user`s table
     *
     * @param $user_id - integer|null
     */
    protected function getOnlyFavoriteRows($user_id)
    {
        //note: not subquery because tables in different databases or even servers.
        $favorite_ids = FavoriteRow::where('table_id', $this->table->id)
            ->where('user_id', $user_id)
            ->select('row_id')
            ->get()
            ->pluck('row_id');

        $this->query->whereIn($this->getSqlFld('id'), $favorite_ids);
    }

    /**
     * Get RowsIds with provided clauses.
     *
     * @param array $data
     * @param $user_id
     * @return array
     */
    public function getRowsIds(array $data, $user_id)
    {
        $this->applyWhereClause($data, $user_id);

        return $this->query
            ->select($this->getSqlFld('id'))
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Apply sorting to the query
     *
     * @param array $sort :
     * [
     *   [field => 'fld', direction => 'asc'],
     *   ...
     * ]
     */
    public function applySorting(array $sort)
    {
        if (count($sort)) {
            foreach ($sort as $srt_elem) {
                $this->query->orderBy($srt_elem['field'], $srt_elem['direction']);
            }
        }
        if ($this->table->is_system != 1) {
            $this->query->orderBy('row_order');
        }
    }

    /**
     * Apply selected RowGroup.
     *
     * @param int $row_group_id
     */
    public function applySelectedRowGroup(int $row_group_id)
    {
        $row_group = $this->table
            ->_row_groups()
            ->where('id', '=', $row_group_id)
            ->first();

        if ($row_group) {
            $this->applyRowGroupsToQuery($this->query, new EloquentCollection([$row_group]));
        }
    }
}