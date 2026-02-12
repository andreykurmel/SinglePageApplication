<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Vanguard\Classes\BiPivotMaker;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartTab;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableChartRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;

class ChartService
{
    protected $tableDataRepository;
    protected $chartRepository;
    protected $service;

    protected $limit_points = 1000;
    protected $limit_excep = 'Too much points, try to use another field. Max points on the chart = 1000.';
    protected $str_excep = 'It seems that you are using `value` functions with `String` column as Y-axis. You can try to check `Ignore Strings`.';

    protected $max_vert_hor = 5;

    /**
     * ChartService constructor.
     */
    public function __construct()
    {
        $this->tableDataRepository = new TableDataRepository();
        $this->chartRepository = new TableChartRepository();
        $this->service = new HelperService();
    }

    /**
     * @param TableChart $chart
     * @param array $settings
     * @return array
     */
    public function prepareChartSettings(TableChart $chart, array $settings): array
    {
        $settings['data_range'] = $settings['data_range'] ?? $chart->_tab->chart_data_range;

        return $settings;
    }

    /**
     * Get Chart (or Create new).
     *
     * @param array $data : [
     *      'id' => int,
     *      'table_id' => $table->id,
     *      'user_id' => auth()->id(),
     *      'row_idx' => $request->row_idx,
     *      'col_idx' => $request->col_idx,
     *      'chart_settings' => $request->chart_settings,
     * ]
     * @return TableChart (exists or created)
     */
    public function getChart(array $data)
    {
        $ch = $this->chartRepository->getChart($data['id']);
        if (!$ch) {
            $ch = $this->chartRepository->createChart($data);
        }
        return $ch;
    }

    /**
     * @param Table $table
     * @param array $charts
     * @return bool
     */
    public function realignCharts(Table $table, array $charts)
    {
        foreach ($charts as $ch) {
            $this->chartRepository->realignChart(
                $ch['id'],
                $table->id,
                $ch['row_idx'],
                $ch['col_idx'],
                $ch['chart_settings']
            );
        }
        return true;
    }

    /**
     * Get Chart by Id
     *
     * @param $id
     * @return TableChart (exists)
     */
    public function getChartId($id)
    {
        return $this->chartRepository->getChart($id);
    }

    /**
     * getChartAndTableData
     *
     * @param Table $table
     * @param array $all_settings
     * @param array $request_params
     * @param int $idx
     * @return array
     */
    public function getChartAndTableData(Table $table, array $all_settings, array $request_params, int $idx = null)
    {
        if ($all_settings['bi_chart']['chart_sub_type'] === 'TSLH') {
            $ch_data = $all_settings['elem_type'] == 'bi_chart'
                ? $this->getXrangeData($table, $all_settings, $request_params)
                : [];
        } else {
            $ch_data = $all_settings['elem_type'] == 'bi_chart'
                ? $this->getChartData($table, $all_settings, $request_params)
                : [];
        }

        $pivot_data = $all_settings['elem_type'] == 'pivot_table'
            ? $this->getPivotData($table, $all_settings, $request_params, $idx)
            : [];

        $pivot_data = $this->convertPivot($table, $all_settings['pivot_table'], $pivot_data);

        return [$ch_data, $pivot_data];
    }

    /**
     * Attach Users and DDL show/val.
     *
     * @param Table $table
     * @param array $pivot_settings
     * @param array $pivot_data
     * @return array
     */
    protected function convertPivot(Table $table, array $pivot_settings, array $pivot_data)
    {
        $with_attachs = collect([]);

        $corresp = [];
        for ($i = 1; $i <= $this->max_vert_hor; $i++) {
            $corresp['hor_l' . $i] = $pivot_settings['horizontal']['l' . $i . '_field'] ?? null;
            $corresp['vert_l' . $i] = $pivot_settings['vertical']['l' . $i . '_field'] ?? null;
        }

        foreach ($pivot_data as $i => $data) {
            $arr = [];
            foreach ($corresp as $bi => $db) {
                if ($db) {
                    $arr[$db] = $data[$bi];
                }
            }
            $with_attachs[$i] = new TableData($arr);
        }

        $with_attachs = (new TableDataRowsRepository())->attachSpecialFields($with_attachs, $table, null, ['users', 'refs']);

        foreach ($with_attachs as $i => $attach) {
            $attach = $attach->toArray();
            foreach ($corresp as $bi => $db) {

                $pivot_data[$i]['_db_' . $bi] = $db;

                if (!empty($attach['_u_' . $db])) {
                    $pivot_data[$i]['_u_' . $bi] = $attach['_u_' . $db];
                }
                if (!empty($attach['_rc_' . $db])) {
                    $pivot_data[$i]['_rc_' . $bi] = $attach['_rc_' . $db];
                }
            }
        }

        return $pivot_data;
    }

    /**
     * Get Xrange Chart Data.
     *
     * @param Table $table
     * @param array $all_settings
     * @param array $request_params
     * @return array
     */
    protected function getXrangeData(Table $table, array $all_settings, array $request_params)
    {
        $chart_settings = $all_settings['bi_chart'];
        $top = $chart_settings['tslh']['top'] ?? '';
        $bottom = $chart_settings['tslh']['bottom'] ?? '';
        $left = $chart_settings['tslh']['start'] ?? '';
        $right = $chart_settings['tslh']['end'] ?? '';
        //no main fields
        if (!$top || !$bottom || !$left || !$right) {
            return [];
        }

        //get SQL for data loading
        $sql = $this->getSqlChart($table, $request_params, $all_settings);
        //start Chart Grouping
        return $this->loadDataXrange($table, $sql, $chart_settings);
    }

    /**
     * @param Table $table
     * @param array $request_params
     * @param array $all_settings
     * @param bool $is_chart
     * @return Builder
     */
    protected function getSqlChart(Table $table, array $request_params, array $all_settings, bool $is_chart = true)
    {
        $excluded_vals = ['hors' => [], 'verts' => []];
        if ($is_chart) {
            $excluded_vals['hors'] = $all_settings['excluded_hors'] ?? [];
            $excluded_vals['verts'] = $all_settings['excluded_verts'] ?? [];
        }
        $sql = $this->tableDataRepository->getSqlForChart($table, $request_params, $excluded_vals, $all_settings['data_range'] ?? '0');

        $wrap = (new TableDataQuery($table))->getQuery();
        $wrap->fromSub($sql->getQuery(), $table->db_name);

        return $wrap;
    }

    /**
     * Load Data for Xrange Chart.
     *
     * @param Table $table
     * @param Builder $sql
     * @param array $chart_settings
     * @return array
     * @throws Exception
     */
    protected function loadDataXrange(Table $table, Builder $sql, array $chart_settings)
    {
        $top = $chart_settings['tslh']['top'] ?? '';
        $bottom = $chart_settings['tslh']['bottom'] ?? '';
        $left = $chart_settings['tslh']['start'] ?? '';
        $right = $chart_settings['tslh']['end'] ?? '';
        $tooltip1_fld = TableField::find($chart_settings['tslh']['tooltip1'] ?? 0);
        $tooltip2_fld = TableField::find($chart_settings['tslh']['tooltip2'] ?? 0);
        $front_fld = TableField::find($chart_settings['tslh']['front_fld'] ?? 0);

        $selectFields = [
            $top . ' as `top`',
            $bottom . ' as `bottom`',
            $left . ' as `x`',
            $right . ' as `x2`',
            ($tooltip1_fld ? $tooltip1_fld->field : '""') . ' as `tooltip1`',
            ($tooltip2_fld ? $tooltip2_fld->field : '""') . ' as `tooltip2`',
            ($front_fld ? $front_fld->field : '""') . ' as `front_fld`',
        ];
        $sql->selectRaw(implode(',', $selectFields));

        $res = $sql->get();
        if ($res->count() < $this->limit_points) {
            $res = $res->toArray();
            foreach ($res as &$row) {
                $row['tooltip1'] = ($tooltip1_fld ? $tooltip1_fld->name . ': ' : '') . $row['tooltip1'];
                $row['tooltip2'] = ($tooltip2_fld ? $tooltip2_fld->name . ': ' : '') . $row['tooltip2'];
            }
            return $res;
        } else {
            throw new Exception('Chart exception. ' . $this->limit_excep, 1);
        }
    }

    /**
     * Get Chart Data.
     *
     * @param Table $table
     * @param array $all_settings
     * @param array $request_params
     * @return array
     */
    protected function getChartData(Table $table, array $all_settings, array $request_params)
    {
        $chart_settings = $all_settings['bi_chart'];
        $x_field = $chart_settings['x_axis']['field'] ?? '';
        $y_field = $chart_settings['y_axis']['field'] ?? '';
        //no main fields
        if (!$x_field || !$y_field) {
            return [];
        }

        //get SQL for data loading
        $sql = $this->getSqlChart($table, $request_params, $all_settings);

        //Chart Grouping
        $result = [];
        if ($groups = $this->groupFuncIsArray($chart_settings)) {
            foreach ($groups as $group) {
                $result = array_merge($result, $this->groupChartData($table, $sql, $chart_settings, $group));
            }
        } else {
            $result = $this->groupChartData($table, $sql, $chart_settings);
        }

        return $result;
    }

    /**
     * @param array $chart_settings
     * @return array
     */
    protected function groupFuncIsArray(array $chart_settings): array
    {
        $calc_val = $chart_settings['y_axis']['calc_val'] ?? 0;
        $group_arr = explode(',', strtolower($chart_settings['y_axis']['group_function'] ?? 'sum'));
        if ($calc_val > 0 && count($group_arr) > 1 && !$chart_settings['x_axis']['l1_group_fld'] ?? '') {
            return $group_arr;
        }
        return [];
    }

    /**
     * Get Chart Data.
     *
     * @param Table $table
     * @param Builder $sql
     * @param array $chart_settings
     * @return array
     * @throws Exception
     */
    protected function groupChartData(Table $table, Builder $sql, array $chart_settings, string $foreGroup = '')
    {
        $x_field = $this->getDbField($table, $chart_settings['x_axis']['field']);
        $y_field = $this->getDbField($table, $chart_settings['y_axis']['field']);
        $l1_field = $this->getDbField($table, $chart_settings['x_axis']['l1_group_fld'] ?? '');
        $l2_field = $this->getDbField($table, $chart_settings['x_axis']['l2_group_fld'] ?? '');
        $group_func = $this->getGroupFunc($chart_settings['y_axis'], $y_field, $foreGroup);

        $select = [
            "$group_func as `y`",
            $x_field . ' as `x`',
            ($l1_field ?: '"'.ucfirst($foreGroup).'"') . ' as `l1`',
            ($l2_field ?: '""') . ' as `l2`',
        ];

        $grouping = array_filter([$x_field, $l1_field, $l2_field]);

        $sql->selectRaw(implode(',', $select));
        $sql->groupBy($grouping);

        $rows = $sql->get();
        if ($rows->count() >= $this->limit_points) {
            throw new Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        $rows = $rows->toArray();

        //Remove 'null'
        foreach ($rows as $idx => $row) {
            is_null($row['x']) ? $rows[$idx]['x'] = '' : null;
            is_null($row['l1']) ? $rows[$idx]['l1'] = '' : null;
            is_null($row['l2']) ? $rows[$idx]['l2'] = '' : null;
        }

        $rows = $this->explodeMSelect($table, $chart_settings, $rows, 'chart');

        if (count($rows) >= $this->limit_points) {
            throw new Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        return $rows;
    }

    /**
     * @param array $settings_elem
     * @param string $y_field
     * @param string $foreGroup
     * @return string
     */
    protected function getGroupFunc(array $settings_elem, string $y_field, string $foreGroup = '')
    {
        $calc_val = $settings_elem['calc_val'] ?? 0;

        if ($calc_val > 0) {
            $group_func = strtolower($foreGroup ?: $settings_elem['group_function'] ?? 'sum');
        } elseif ($calc_val < 0) {
            $group_func = 'count_distinct';
        } else {
            $group_func = 'count';
        }

        //#app_avail_formulas
        switch ($group_func) {
            case 'count_distinct':
                $func = "count(distinct $y_field)";
                break;
            case 'count':
                $func = "count($y_field)";
                break;
            case 'sum':
                $func = "sum($y_field)";
                break;
            case 'min':
                $func = "min($y_field)";
                break;
            case 'max':
                $func = "max($y_field)";
                break;
            case 'avg':
                $func = "avg($y_field)";
                break;
            case 'mean':
                $func = "(min($y_field) + max($y_field))/2";
                break;
            case 'var':
                $func = "variance($y_field)";
                break;
            case 'std':
                $func = "std($y_field)";
                break;
            default:
                $func = "count($y_field)";
                break;
        }

        return $func;
    }

    /**
     * @param Table $table
     * @param string $settings_elem
     * @return string
     */
    protected function getDbField(Table $table, string $settings_elem)
    {
        return $settings_elem
            ? $this->service->convertSysField($table, $settings_elem, true)
            : '';
    }

    /**
     * Get Table Pivot Data.
     *
     * @param Table $table
     * @param array $all_settings
     * @param array $request_params
     * @param int $idx
     * @return array
     */
    protected function getPivotData(Table $table, array $all_settings, array $request_params, int $idx = null)
    {
        $pivot_settings = $all_settings['pivot_table'];
        $key = 'about' . ($idx ? '_' . $idx : '');
        //no main field
        if (!$pivot_settings[$key]['field']) {
            return [];
        }

        //get SQL for data loading
        $sql = $this->getSqlChart($table, $request_params, $all_settings, false);
        //Chart Grouping
        return $this->groupPivotData($table, $sql, $pivot_settings, $pivot_settings[$key]);
    }

    /**
     * Get Pivot Table Data.
     *
     * @param Table $table
     * @param Builder $sql
     * @param array $pivot_settings
     * @param array $about
     * @return array
     * @throws Exception
     */
    protected function groupPivotData(Table $table, Builder $sql, array $pivot_settings, array $about)
    {
        if ($about['abo_type'] == 'formula') {
            $y_field = (new FormulaEvaluatorRepository($table))->asSqlString($about['formula_string'] ?? '');
        } else {
            $y_field = $this->getDbField($table, $about['field']);
        }
        $group_func = $y_field ? $this->getGroupFunc($about, $y_field) : '""';

        $select = ["$group_func as `y`"];
        $grouping = [];

        if ($lbl_field = $this->getDbField($table, $about['label_field'] ?? '')) {
            $select[] = "`$lbl_field` as `lbl`";
            $grouping[] = $lbl_field;
        }

        for ($i = 1; $i <= $this->max_vert_hor; $i++) {
            $hor = $this->getDbField($table, $pivot_settings['horizontal']['l' . $i . '_field'] ?? '');
            $select[] = ($hor ? '`' . $hor . '`' : '""') . ' as `hor_l' . $i . '`';
            if ($hor) $grouping[] = $hor;

            $vert = $this->getDbField($table, $pivot_settings['vertical']['l' . $i . '_field'] ?? '');
            $select[] = ($vert ? '`' . $vert . '`' : '""') . ' as `vert_l' . $i . '`';
            if ($vert) $grouping[] = $vert;
        }

        $sql->selectRaw(implode(', ', $select));
        $sql->groupBy($grouping ? array_unique($grouping) : 'id');

        $res = $sql->get();
        if ($res->count() >= $this->limit_points) {
            throw new Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        $res = $res->toArray();

        //Just for DDL 'id/show'
        if ($pivot_settings['referenced_tables'] ?? false) {
            for ($i = 1; $i <= $this->max_vert_hor; $i++) {
                $res = $this->pivot_ref_data($res, $pivot_settings, 'horizontal', $i);
                $res = $this->pivot_ref_data($res, $pivot_settings, 'vertical', $i);
            }
        }

        //Remove 'null'
        foreach ($res as $idx => $row) {
            for ($i = 1; $i <= $this->max_vert_hor; $i++) {
                is_null($row['hor_l' . $i]) ? $res[$idx]['hor_l' . $i] = '' : null;
                is_null($row['vert_l' . $i]) ? $res[$idx]['vert_l' . $i] = '' : null;
            }
        }

        $res = $this->explodeMSelect($table, $pivot_settings, $res, 'pivot');

        if (count($res) >= $this->limit_points) {
            throw new Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        return $res;
    }

    /**
     * @param array $rows
     * @param array $pivot_settings
     * @param string $type
     * @param int $i
     * @return array
     */
    protected function pivot_ref_data(array $rows, array $pivot_settings, string $type, int $i)
    {
        $rref = $pivot_settings[$type]['l' . $i . '_reference'] ?? '';
        $refFld = is_numeric($rref) ? (new TableFieldRepository())->getField($rref) : null;
        if ($refFld && $refFld->_table) {
            $part = $type == 'horizontal' ? 'hor_l' : 'vert_l';
            $targetFld = $pivot_settings[$type]['l' . $i . '_ref_link'] ?? 'id';
            $clients = (new TableDataQuery($refFld->_table))->getQuery()->whereIn($targetFld, Arr::pluck($rows, $part . $i))->get();
            foreach ($rows as $idx => $row) {
                $found = $clients->where($targetFld, '=', $row[$part . $i])->first();
                if ($found) {
                    $rows[$idx][$part . $i] = $found[$refFld->field];
                }
            }
        }
        return $rows;
    }

    /**
     * @param Table $table
     * @param array $pivot_settings
     * @param array $res
     * @param string $type
     * @return array
     */
    protected function explodeMSelect(Table $table, array $pivot_settings, array $res, string $type = 'pivot')
    {
        if ($type == 'pivot') {
            $some_split = $pivot_settings['horizontal']['l1_split'] || $pivot_settings['horizontal']['l2_split'] || $pivot_settings['horizontal']['l3_split']
                || $pivot_settings['vertical']['l1_split'] || $pivot_settings['vertical']['l2_split'] || $pivot_settings['vertical']['l3_split'];
        } else {
            $some_split = true;
        }

        if ($some_split) {
            $new_arr = [];

            if ($type == 'pivot') {
                foreach ($res as $i => $row) {
                    $hors = [];
                    $verts = [];
                    for ($j = 1; $j <= $this->max_vert_hor; $j++) {
                        $hors[$j] = $this->parseMSel($pivot_settings['horizontal']['l'.$j.'_split'], $res[$i]['hor_l'.$j]);
                        $verts[$j] = $this->parseMSel($pivot_settings['vertical']['l'.$j.'_split'], $res[$i]['vert_l'.$j]);
                    }
                    $new_arr = $this->buildResPivot($new_arr, $hors, $verts, $res[$i]['y']);
                }
            } else {
                foreach ($res as $i => $row) {
                    $x = $this->parseMSel(true, $res[$i]['x']);
                    $l1 = $this->parseMSel(true, $res[$i]['l1']);
                    $l2 = $this->parseMSel(true, $res[$i]['l2']);
                    $new_arr = $this->buildResChart($new_arr, $x, $l1, $l2, $res[$i]['y']);
                }
            }

        } else {
            $new_arr = $res;
        }
        return $new_arr;
    }

    /**
     * @param $can_split
     * @param $val
     * @return array
     */
    protected function parseMSel($can_split, $val)
    {
        if ($can_split && $val && $val[0] == '[') {
            return explode(',', preg_replace('/["\[\]]/i', '', $val));
        } else {
            return [$val];
        }
    }

    /**
     * @param array $new_arr
     * @param array $hors
     * @param array $verts
     * @param $y
     * @return array
     */
    protected function buildResPivot(array $new_arr, array $hors, array $verts, $y)
    {
        foreach ($hors[1] as $h1) {
            foreach ($hors[2] as $h2) {
                foreach ($hors[3] as $h3) {
                    foreach ($hors[4] as $h4) {
                        foreach ($hors[5] as $h5) {
                            foreach ($verts[1] as $v1) {
                                foreach ($verts[2] as $v2) {
                                    foreach ($verts[3] as $v3) {
                                        foreach ($verts[4] as $v4) {
                                            foreach ($verts[5] as $v5) {
                                                $new_arr[] = [
                                                    'hor_l1' => $h1,
                                                    'hor_l2' => $h2,
                                                    'hor_l3' => $h3,
                                                    'hor_l4' => $h4,
                                                    'hor_l5' => $h5,
                                                    'vert_l1' => $v1,
                                                    'vert_l2' => $v2,
                                                    'vert_l3' => $v3,
                                                    'vert_l4' => $v4,
                                                    'vert_l5' => $v5,
                                                    'y' => $y,
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $new_arr;
    }

    /**
     * @param array $new_arr
     * @param array $x
     * @param array $l1
     * @param array $l2
     * @param $y
     * @return array
     */
    protected function buildResChart(array $new_arr, array $x, array $l1, array $l2, $y)
    {
        foreach ($x as $h1) {
            foreach ($l1 as $h2) {
                foreach ($l2 as $h3) {
                    $new_arr[] = [
                        'x' => $h1,
                        'l1' => $h2,
                        'l2' => $h3,
                        'y' => $y,
                    ];
                }
            }
        }
        return $new_arr;
    }

    /**
     * Save or Update Chart Settings.
     *
     * @param array $data : [
     *      'id' => int,
     *      'table_id' => $table->id,
     *      'user_id' => auth()->id(),
     *      'row_idx' => $request->row_idx,
     *      'col_idx' => $request->col_idx,
     *      'chart_settings' => $request->chart_settings,
     * ]
     * @param TableChart $chart
     * @param string $param_name
     * @return TableChart
     */
    public function saveChart(TableChart $chart, array $data, string $param_name)
    {
        return $this->chartRepository->updateChart($chart->id, $data, $param_name);
    }

    /**
     * Delete Chart.
     *
     * @param array $data : [
     *      'id' => int,
     *      'table_id' => $table->id,
     *      'user_id' => auth()->id(),
     *      'row_idx' => $request->row_idx,
     *      'col_idx' => $request->col_idx,
     *      'chart_settings' => $request->chart_settings,
     * ]
     * @return integer
     */
    public function delChart(array $data)
    {
        if ($ch = $this->chartRepository->getChart($data['id'])) {
            $this->chartRepository->delChart($ch->id);
        }
        return 1;
    }

    /**
     * @param Table $chart_table
     * @param array $chart_settings
     * @param Table $target_table
     * @param array $request_params
     * @return array
     */
    public function exportChart(Table $chart_table, array $chart_settings, Table $target_table, array $request_params)
    {
        [$chart_data, $pivot_data] = $this->getChartAndTableData($chart_table, $chart_settings, $request_params);

        $maker = new BiPivotMaker($chart_table, $chart_settings, $pivot_data);
        $headers = $maker->getHeaderNames();
        $rows = $maker->getDataRows();

        $import = new ImportService();
        $result = $import->modifyTable($target_table, $import->datasForChartExport($headers, $rows));

        $this->chartRepository->updateChartSettings($chart_settings['id'], ['table_to_export' => $result['new_id']]);
        return $result;
    }

    /**
     * @param TableChart $chart
     * @param array $settings
     * @return array
     */
    public function updateChartSettings(TableChart $chart, array $settings)
    {
        return [
            'result' => $this->chartRepository->updateChartSettings($chart->id, $settings),
        ];
    }

    /**
     * @param TableChart $chart
     * @param int $table_permis_id
     * @param $can_edit
     * @return mixed
     */
    public function toggleChartRight(TableChart $chart, int $table_permis_id, $can_edit)
    {
        return $this->chartRepository->toggleChartRight($chart, $table_permis_id, $can_edit);
    }

    /**
     * @param TableChart $chart
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteChartRight(TableChart $chart, int $table_permis_id)
    {
        return $this->chartRepository->deleteChartRight($chart, $table_permis_id);
    }

    /**
     * @param int $model_id
     * @return TableChartTab
     */
    public function getTab(int $model_id)
    {
        return $this->chartRepository->getTab($model_id);
    }

    /**
     * @param array $data
     * @return TableChartTab
     */
    public function insertTab(array $data)
    {
        return $this->chartRepository->insertTab($data);
    }

    /**
     * @param $model_id
     * @param array $data
     * @return mixed
     */
    public function updateTab($model_id, array $data)
    {
        return $this->chartRepository->updateTab($model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTab($model_id)
    {
        return $this->chartRepository->deleteTab($model_id);
    }

    /**
     * @param Table $table
     * @return void
     */
    public function emptyChartsToTab(Table $table): void
    {
        $this->chartRepository->emptyChartsToTab($table);
    }
}