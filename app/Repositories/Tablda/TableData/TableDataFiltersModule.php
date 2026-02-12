<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Vanguard\Classes\DropdownHelper;
use Vanguard\Models\DDL;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Services\Tablda\HelperService;

class TableDataFiltersModule
{
    protected $service;

    protected $tdq;
    protected $table;
    protected $query_sql;
    protected $rowgroup_sql;
    protected $max_el;

    /**
     * TableDataFiltersModule constructor.
     *
     * @param TableDataQuery $tdq
     */
    public function __construct(TableDataQuery $tdq)
    {
        $this->tdq = $tdq;
        $this->table = $tdq->getTable();
        $this->max_el = $this->table->max_filter_elements ?: 1000;

        $this->query_sql = clone $tdq->getQuery();
        $this->query_sql->distinct();

        if ($tdq->hidden_row_groups) {
            $this->rowgroup_sql = (clone $tdq->getQuery())->distinct();
            $tdq->applyHiddenRowGroups($this->rowgroup_sql, $tdq->hidden_row_groups);
        }

        $this->service = new HelperService();
    }

    /**
     * @param Model $filter
     * @return array
     */
    protected function filterFields(Model $filter): array
    {
        return $filter->only([
            'id',
            'name',
            'field',
            'ddl_id',
            'filter_type',
            'filter_search',
            'f_type',
            'input_type',
            'applied_index',
            'values',
            'error_msg',
            'clarify_searching'
        ]);
    }

    /**
     * get filters for user`s table.
     * values for not applied filters are only those, which present after applying filters.
     * so if applied filter by 'State=AL', then in 'City' filter will be present cities only for 'State=AL'.
     *
     * @param array $data // as in TableDataRepository::getRows()
     *
     * @return array:
     * [
     *  0 => [
     *      id: int,
     *      field: string,
     *      name: string,
     *      ddl_id: int,
     *      applied_index: int,
     *      filter_type: string,
     *      filter_search: string,
     *      f_type: string,
     *      input_type: string,
     *      error_msg: string,
     *      values: [
     *          0 => [
     *              checked: bool,
     *              val: string,
     *              show: string,
     *              rowgroup_disabled: bool
     *          ],
     *          ...
     *              OR
     *          'min' => [
     *              val: float,
     *              selected: float,
     *          ],
     *          'max' => [
     *              val: float,
     *              selected: float,
     *          ]
     *      ]
     *  ],
     *  ...
     * ]
     */
    public function getFilters(array $data): array
    {
        //Visitor or user in View can activate temp filters.
        $applied_filter_fields = empty($data['user_id'])
            ? Arr::pluck($data['applied_filters'] ?? [], 'field')
            : [];

        //get Table Filters
        $filters = $this->table
            ->_fields
            ->filter(function ($value, $key) use ($applied_filter_fields) {
                return $applied_filter_fields
                    ? in_array($value['field'], $applied_filter_fields)
                    : $value['filter'] == 1;
            })
            ->values();

        //select only with 'applied_index'
        $applied_filters = $this->prepareApplied($data['applied_filters'] ?? []);
        $present_ids = Arr::pluck($applied_filters, 'id');

        if (!count($filters)) {
            return [];
        }

        //fill applied filters
        $applied_index = 0;
        foreach ($applied_filters as $a_filter) {
            $filter = $filters->where('field', '=', $a_filter['field'])->first();
            //if applied filter is present in active filters
            if ($filter) {
                $filter->filter_type = $filter->filter_type ?: 'value';
                if ($filter->filter_type == 'value') {
                    $filter = $this->getAppliedFilterValues($filter, $a_filter['values'], ++$applied_index);
                }
                if ($filter->filter_type == 'range') {
                    $filter = $this->getFilterRanges($filter, $a_filter['values'], ++$applied_index);
                }
            }
        }

        //fill not applied filters
        foreach ($filters as $key => $filter) {
            $filter->filter_type = $filter->filter_type ?: 'value';
            if (!in_array($filter->id, $present_ids)) {
                if ($filter->filter_type == 'value') {
                    //Initial view 'Blank' works for Table only if filters are present
                    $filter = $this->getFilterValues($filter, ['first_init_view' => $data['first_init_view'] ?? 0]);
                }
                if ($filter->filter_type == 'range') {
                    $filter = $this->getFilterRanges($filter, []);
                }

            }

            $filters[$key] = $this->filterFields($filter);
        }

        return $filters->toArray();
    }

    /**
     * Get only applied filters (sorted by 'applied_index') with only checked values.
     *
     * @param array $filters
     * @return array
     */
    public function prepareApplied(array $filters)
    {
        //get only applied filters
        $filters = array_filter($filters, function ($elem) {
            return ($elem['applied_index'] ?? null) > 0;
        });
        //get only checked values in filter_type = 'value'
        foreach ($filters as &$elem) {
            if (($elem['filter_type'] ?? 'value') == 'value') {
                $elem['values'] = array_filter($elem['values'], function ($val) {
                    return !!$val['checked'] ?? '';
                });
                $elem['values'] = $this->removeNulls($elem['values']);
            }
        };
        //sort filters by 'applied'index'
        usort($filters, function ($a, $b) {
            return $a['applied_index'] <=> $b['applied_index'];
        });
        return $filters;
    }

    /**
     * @param array $values
     * @return array
     */
    protected function removeNulls(array $values)
    {
        foreach ($values as &$el) {
            $el['val'] = $el['val'] ?? '';
            $el['show'] = $el['show'] ?? '';
        }
        return $values;
    }

    /**
     * Get Applied Filter with type 'value'.
     *
     * @param $filter
     * @param array $checked_values
     * @param int $applied_index
     * @return mixed
     */
    protected function getAppliedFilterValues($filter, array $checked_values, int $applied_index)
    {
        try {
            $uncheck_rows = $this->getFilterRows($filter, 0, false);

            $check_rows = [];
            foreach ($checked_values as $ar) {
                $check_rows['_' . $ar['val']] = $ar;
            }

            $rows_merged = array_merge($uncheck_rows, $check_rows);
            //some filter elements are disabled by RowGroups
            $rows_merged = $this->syncWithRowGroups($rows_merged, $filter);

            $filter->applied_index = $applied_index;
            $filter->values = $this->sortFilter($filter, $rows_merged);

            //filter values on sub-levels
            $this->whereInMSel($filter, Arr::pluck($checked_values, 'val'));
        } catch (\Exception $exception) {
            $filter->applied_index = 0;
            $filter->values = [];
            $filter->error_msg = $exception->getMessage();
        }
        return $filter;
    }

    /**
     * @param $filter
     * @param array $all_values
     * @return array
     */
    protected function sortFilter($filter, array $all_values): array
    {
        $ddl = $filter->ddl_id ? (new DDLRepository())->getDDL($filter->ddl_id) : null;
        if ($ddl) {
            $all_values = DropdownHelper::ddlSort($ddl, $all_values);
        } else {
            $all_values = array_values(Arr::sort($all_values, 'show'));
        }
        return $all_values;
    }

    /**
     * @param $filter
     * @param array $values
     */
    protected function whereInMSel($filter, array $values)
    {
        $filter_field = $this->service->convertSysField($this->table, $filter->field);
        $filter_field = $this->tdq->getSqlFld($filter_field);
        $applier = new FieldTypeApplier($filter->f_type, $filter_field);

        $this->query_sql->where(function ($inner) use ($filter, $applier, $values) {
            $inner->orWhere(function ($sub) use ($applier, $values) {
                $applier->whereIn($sub, $values);
            });

            if (MselectData::isMSEL($filter->input_type)) {
                foreach ($values as $val) {
                    if ($val) {
                        $inner->orWhere(function ($sub) use ($applier, $val) {
                            $applier->where($sub, 'Like', '%"' . $val . '"%');
                        });
                    }
                }
            }
        });
    }

    /**
     * Get Distinct Rows from Table as FilterValues.
     *
     * @param TableField $filter
     * @param int $status
     * @param bool $is_rg_sql
     * @return array
     * @throws \Exception
     */
    protected function getFilterRows(TableField $filter, int $status, bool $is_rg_sql = false)
    {
        $filter_field = $this->service->convertSysField($this->table, $filter->field);

        $sql = $is_rg_sql ? $this->rowgroup_sql : $this->query_sql;
        $sql->select( $this->tdq->getSqlFld($filter_field) );
        //check max filter elements
        $ignore_limit = $this->table->_fields->where('filter_search', '=', 1)->count();
        if (!$is_rg_sql && $sql->count( $this->tdq->getSqlFld($filter_field) ) > $this->max_el) {
            $msg = 'The number of distinctive values exceeds set limit: ' . $this->max_el . '. The filter is not displayed. Try applying other filter(s) first and/or use SEARCH.';
            if ($ignore_limit) {
                $filter->error_msg = $msg;
            } else {
                throw new \Exception($msg, 1);
            }
        }
        $all_vals = $sql->get()->pluck($filter_field)->toArray();

        //Special converts if input_type is Multi-select
        $all_vals = MselectData::convert($all_vals, $filter->input_type);

        //Special converts if f_type in 'User','RefTable','RefField'
        $all_vals = $this->prepareFtypes($all_vals, $filter);

        //change structure type
        $res_rows = [];
        foreach ($all_vals as $elem) {
            $res_rows['_' . $elem['val']] = [
                'checked' => $status,
                'val' => $elem['val'] ?? '',
                'show' => $elem['show'] ?? '',
                'rowgroup_disabled' => false,
            ];
        }

        return $res_rows;
    }

    /**
     * Special converts if input_type is Multi-select
     *
     * @param array $all_vals
     * @param string $input_type
     * @return array
     */
    protected function MselectConverting(Array $all_vals, string $input_type)
    {
        if (MselectData::isMSEL($input_type)) {
            $uniqued = [];
            foreach ($all_vals as $val) {
                $arr = json_decode($val, 1);
                if ($arr) {
                    $uniqued = array_merge($uniqued, $arr);
                } else {
                    $uniqued = array_merge($uniqued, [$val]);
                }
            }
            $all_vals = [];
            foreach (array_unique($uniqued) as $val) {
                $all_vals[] = $val;
            };
        }
        return $all_vals;
    }

    /**
     * Special converts for some Field Types
     *
     * @param $all_vals
     * @param $filter
     * @return array : [
     *      ['val' => string, 'show' => string],
     *      ...
     * ]
     */
    protected function prepareFtypes($all_vals, $filter)
    {
        $new_vals = [];
        //'User' type
        if ($filter->f_type == 'User') {
            $all_vals = MselectData::convert($all_vals, $filter->input_type);
            $show_settings = $this->table->_owner_settings;
            [$users, $groups] = (new TableDataRowsRepository())->getUsersAndGroups( collect($all_vals) );
            foreach ($all_vals as $val) {
                $usr = $users->where('id', '=', $val)->first();
                if (!$usr) {
                    $gr = $groups->where('id', '=', $val)->first();
                    $show = ($gr ? $gr->first_name . ' ' . $gr->last_name : $val);
                } else {
                    $show = $show_settings->getUserFilterStr($usr) ?: $val;
                }
                $new_vals[] = [
                    'val' => $val,
                    'show' => $show,
                ];
            };
        // 'TableId' type
        } elseif ($filter->f_type == 'TableId') {
            $tables = Table::whereIn('id', $all_vals)->get();
            foreach ($all_vals as $val) {
                $tb = $tables->where('id', '=', $val)->first();
                $new_vals[] = [
                    'val' => $val,
                    'show' => $tb ? $tb->name : $val,
                ];
            };
        // for CORRESPONDENCE FIELDS table
        } elseif ($filter->f_type == 'RefTable') {
            $tables = Table::whereIn('db_name', $all_vals)->get();
            foreach ($all_vals as $val) {
                $tb = $tables->where('db_name', '=', $val)->first();
                $new_vals[] = [
                    'val' => $val,
                    'show' => $tb ? $tb->name : $val,
                ];
            };
        } elseif ($filter->f_type == 'RefField') {
            $table_fields = TableField::whereIn('field', $all_vals)->get();
            foreach ($all_vals as $val) {
                $tb_fld = $table_fields->where('field', '=', $val)->first();
                $new_vals[] = [
                    'val' => $val,
                    'show' => $tb_fld ? preg_replace('/,/i','', $tb_fld->name) : $val,
                ];
            };
        // for CORRESPONDENCE FIELDS table ^^^^^
        }
        // DDL with 'id' in RefConds
        elseif (
            $filter->ddl_id
            &&
            DDL::where('id', '=', $filter->ddl_id)->hasIdReferences()->count()
        ) {
            $refer_rows = (new TableDataRowsRepository())
                ->getReferencedRowsFromDdl(collect($all_vals), $filter->ddl_id)
                ->map(function ($item) {
                    return [
                        'val' => $item->init_val,
                        'show' => $item->show_val,
                    ];
                })
                ->toArray();

            $new_vals = array_merge($new_vals, $refer_rows);
        }

        //No special actions
        $is_id_val = !!$new_vals;
        if ($new_vals) {
            $new_vals_pluck = Arr::pluck($new_vals, 'val');
            $all_vals = collect($all_vals)->filter(function ($val) use ($new_vals_pluck) {
                $find = strlen($val) ? $val : '';
                return !in_array($find, $new_vals_pluck);
            });
        }
        foreach ($all_vals as $val) {
            $new_vals[] = [
                'val' => $val,
                'show' => $is_id_val ? "[$val]" : $val,
            ];
        };
        return $new_vals;
    }

    /**
     * some filter elements are disabled by RowGroups
     *
     * @param array $rows
     * @param TableField $filter
     * @return array
     */
    protected function syncWithRowGroups(array $rows, TableField $filter)
    {
        if ($this->rowgroup_sql) {
            $not_hidden_filter_values = $this->getFilterRows($filter, 0, true);
            foreach ($rows as $k => $obj) {
                if (empty($not_hidden_filter_values[$k])) {
                    $rows[$k]['rowgroup_disabled'] = true;
                    //$rows[$k]['checked'] = 0;
                }
            }
        }
        return $rows;
    }

    /**
     * Get Applied Filter with type 'value' or 'range'.
     *
     * @param $filter
     * @param array $selected_vals
     * @param int|null $applied_index
     * @return mixed
     */
    protected function getFilterRanges($filter, array $selected_vals, int $applied_index = null)
    {
        try {
            $filter_field_sql = $this->service->convertSysField($this->table, $filter->field);
            $filter_field_sql = $this->tdq->getSqlFld($filter_field_sql);

            $sql = clone $this->query_sql;
            $this->tdq->applyHiddenRowGroups($sql, $this->tdq->hidden_row_groups);

            if (in_array($filter->f_type, ['Date', 'Date Time'])) {
                $min_val = $sql->select(DB::raw(' MIN(' . $filter_field_sql . ') as mval '))->first();
                $max_val = $sql->select(DB::raw(' MAX(' . $filter_field_sql . ') as mval '))->first();
            } else {
                $min_val = $sql->select(DB::raw(' MIN(' . $filter_field_sql . ' + 0) as mval '))->first();
                $max_val = $sql->select(DB::raw(' MAX(' . $filter_field_sql . ' + 0) as mval '))->first();
            }

            $values = ['min' => [], 'max' => []];
            $values['min']['val'] = $min_val ? $min_val->mval : null;
            $values['min']['selected'] = $selected_vals['min']['selected'] ?? PHP_INT_MIN;
            $values['min']['selected'] = $values['min']['selected'] < $values['min']['val'] ? $values['min']['val'] : $values['min']['selected'];
            $values['max']['val'] = $max_val ? $max_val->mval : null;
            $values['max']['selected'] = $selected_vals['max']['selected'] ?? PHP_INT_MAX;
            $values['max']['selected'] = $values['max']['selected'] > $values['max']['val'] ? $values['max']['val'] : $values['max']['selected'];

            $filter->values = $values;

            if (!is_null($applied_index)) {
                $filter->applied_index = $applied_index;
                //filter values on sub-levels
                $this->query_sql->where(function ($q) use ($filter_field_sql, $values) {
                    $q->where($filter_field_sql, '>=', $values['min']['selected']);
                    $q->where($filter_field_sql, '<=', $values['max']['selected']);
                });
            } else {
                $filter->applied_index = 0;
            }

        } catch (\Exception $exception) {
            $filter->applied_index = 0;
            $filter->values = [];
            $filter->error_msg = $exception->getMessage();
        }
        return $filter;
    }

    /**
     * Get not applied Filter with type 'values'.
     *
     * @param $filter
     * @param array $extra_params
     * @return mixed
     */
    protected function getFilterValues($filter, array $extra_params)
    {
        try {
            //checked filters (or unchecked if table has init status 'blank')
            $init_is_blank = !empty($extra_params['first_init_view']) && $extra_params['first_init_view'] == -2;

            $rows = $this->getFilterRows($filter, $init_is_blank ? 0 : 1);
            //some filter elements are disabled by RowGroups
            $rows = $this->syncWithRowGroups($rows, $filter);

            $filter->applied_index = 0;
            $filter->values = $this->sortFilter($filter, $rows);
        } catch (\Exception $exception) {
            $filter->applied_index = 0;
            $filter->values = [];
            $filter->error_msg = $exception->getMessage();
        }
        return $filter;
    }
}