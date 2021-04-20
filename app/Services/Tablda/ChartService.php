<?php

namespace Vanguard\Services\Tablda;

use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableChartRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;

class ChartService
{
    protected $tableRepository;
    protected $tableDataRepository;
    protected $chartRepository;
    protected $service;

    protected $limit_points = 5000;
    protected $limit_excep = 'Too much points, try to use another field. Max points on the chart = 5000.';
    protected $str_excep = 'It seems that you are using `value` functions with `String` column as Y-axis. You can try to check `Ignore Strings`.';

    protected $max_vert_hor = 5;

    /**
     * ChartService constructor.
     */
    public function __construct()
    {
        $this->tableRepository = new TableRepository();
        $this->tableDataRepository = new TableDataRepository();
        $this->chartRepository = new TableChartRepository();
        $this->service = new HelperService();
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
        $ch = $this->tableRepository->getChart($data['id']);
        if (!$ch) {
            $ch = $this->tableRepository->createChart($data);
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
        return $this->tableRepository->getChart($id);
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
            $corresp['hor_l'.$i] = $pivot_settings['horizontal']['l'.$i.'_field'] ?? null;
            $corresp['vert_l'.$i] = $pivot_settings['vertical']['l'.$i.'_field'] ?? null;
        }

        foreach ($pivot_data as $i => $data) {
            $arr = [];
            foreach ($corresp as $bi => $db) {
                if ($db) {
                    $arr[ $db ] = $data[ $bi ];
                }
            }
            $with_attachs[$i] = new TableData($arr);
        }

        $with_attachs = (new TableDataRowsRepository())->attachSpecialFields($with_attachs, $table, null, null, ['users','refs']);

        foreach ($with_attachs as $i => $attach) {
            $attach = $attach->toArray();
            foreach ($corresp as $bi => $db) {

                $pivot_data[$i]['_db_'.$bi] = $db;

                if (!empty($attach['_u_'.$db])) {
                    $pivot_data[$i]['_u_'.$bi] = $attach['_u_'.$db];
                }
                if (!empty($attach['_rc_'.$db])) {
                    $pivot_data[$i]['_rc_'.$bi] = $attach['_rc_'.$db];
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
        $excluded_vals = [ 'hors' => [], 'verts' =>[] ];
        if ($is_chart) {
            $excluded_vals['hors'] = $all_settings['excluded_hors'] ?? [];
            $excluded_vals['verts'] = $all_settings['excluded_verts'] ?? [];
        }
        $row_group_id = $all_settings['dataset']['rowgr_id'] ?? null;
        $sql = $this->tableDataRepository->getSqlForChart($table, $request_params, $excluded_vals, $row_group_id);
        return $sql;
    }

    /**
     * Load Data for Xrange Chart.
     *
     * @param Table $table
     * @param Builder $sql
     * @param array $chart_settings
     * @return array
     * @throws \Exception
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

        $len = $sql->count();

        if ($len < $this->limit_points) {
            $res = $sql->get()->toArray();
            foreach ($res as &$row) {
                $row['tooltip1'] = ($tooltip1_fld ? $tooltip1_fld->name . ': ' : '') . $row['tooltip1'];
                $row['tooltip2'] = ($tooltip2_fld ? $tooltip2_fld->name . ': ' : '') . $row['tooltip2'];
            }
            return $res;
        } else {
            throw new \Exception('Chart exception. ' . $this->limit_excep, 1);
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
        return $this->groupChartData($table, $sql, $chart_settings);
    }

    /**
     * Get Chart Data.
     *
     * @param Table $table
     * @param Builder $sql
     * @param array $chart_settings
     * @return array
     * @throws \Exception
     */
    protected function groupChartData(Table $table, Builder $sql, array $chart_settings)
    {
        $x_field = $this->getDbField($table, $chart_settings['x_axis']['field']);
        $y_field = $this->getDbField($table, $chart_settings['y_axis']['field']);
        $l1_field = $this->getDbField($table, $chart_settings['x_axis']['l1_group_fld'] ?? '');
        $l2_field = $this->getDbField($table, $chart_settings['x_axis']['l2_group_fld'] ?? '');
        $group_func = $this->getGroupFunc($chart_settings['y_axis'], $y_field);

        $select = [
            "$group_func as `y`",
            $x_field . ' as `x`',
            ($l1_field ?: '""') . ' as `l1`',
            ($l2_field ?: '""') . ' as `l2`',
        ];

        $grouping = array_filter([$x_field, $l1_field, $l2_field]);

        $sql->selectRaw(implode(',', $select));
        $sql->groupBy($grouping);

        if ($sql->count() >= $this->limit_points) {
            throw new \Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        return $sql->get()->toArray();
    }

    /**
     * @param array $settings_elem
     * @param string $y_field
     * @return mixed|string
     */
    protected function getGroupFunc(array $settings_elem, string $y_field)
    {
        $calc_val = $settings_elem['calc_val'] ?? 0;

        if ($calc_val > 0) {
            $group_func = strtolower($settings_elem['group_function'] ?? 'sum');
        } elseif ($calc_val < 0) {
            $group_func = 'count_distinct';
        } else {
            $group_func = 'count';
        }

        //#app_avail_formulas
        switch ($group_func) {
            case 'count_distinct': $func = "count(distinct $y_field)";
                break;
            case 'count': $func = "count($y_field)";
                break;
            case 'sum': $func = "sum($y_field)";
                break;
            case 'min': $func = "min($y_field)";
                break;
            case 'max': $func = "max($y_field)";
                break;
            case 'avg': $func = "avg($y_field)";
                break;
            case 'mean': $func = "(min($y_field) + max($y_field))/2";
                break;
            case 'var': $func = "variance($y_field)";
                break;
            case 'std': $func = "std($y_field)";
                break;
            default: $func = "count($y_field)";
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
        $key = 'about' . ($idx ? '_'.$idx : '');
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
     * @throws \Exception
     */
    protected function groupPivotData(Table $table, Builder $sql, array $pivot_settings, array $about)
    {
        $y_field = $this->getDbField($table, $about['field']);
        $group_func = $this->getGroupFunc($about, $y_field);

        $select = [ "$group_func as `y`" ];
        $grouping = [];

        for ($i = 1; $i <= $this->max_vert_hor; $i++) {
            $hor = $this->getDbField($table, $pivot_settings['horizontal']['l'.$i.'_field'] ?? '');
            $select[] = ($hor ?: '""') . ' as `hor_l'.$i.'`';
            if ($hor) $grouping[] = $hor;

            $vert = $this->getDbField($table, $pivot_settings['vertical']['l'.$i.'_field'] ?? '');
            $select[] = ($vert ?: '""') . ' as `vert_l'.$i.'`';
            if ($vert) $grouping[] = $vert;
        }

        $sql->selectRaw(implode(',', $select));
        $sql->groupBy($grouping ?: 'id');

        if ($sql->count() >= $this->limit_points) {
            throw new \Exception('Chart exception. ' . $this->limit_excep, 1);
        }

        $res = $sql->get()->toArray();

        //Remove 'null'
        foreach ($res as $idx => $row) {
            for ($i = 1; $i <= $this->max_vert_hor; $i++) {
                is_null($row['hor_l'.$i]) ? $res[$idx]['hor_l'.$i] = '' : null;
                is_null($row['vert_l'.$i]) ? $res[$idx]['vert_l'.$i] = '' : null;
            }
        }

        return $this->explodeMSelect($table, $pivot_settings, $res);
    }

    /**
     * @param Table $table
     * @param array $pivot_settings
     * @param array $res
     * @return array
     */
    protected function explodeMSelect(Table $table, array $pivot_settings, array $res)
    {
        $some_split = $pivot_settings['horizontal']['l1_split'] || $pivot_settings['horizontal']['l2_split'] || $pivot_settings['horizontal']['l3_split']
            || $pivot_settings['vertical']['l1_split'] || $pivot_settings['vertical']['l2_split'] || $pivot_settings['vertical']['l3_split'];

        if ($some_split) {
            $new_arr = [];
            foreach ($res as $i => $row) {
                $hor_l1 = $this->parseMSel($pivot_settings['horizontal']['l1_split'], $res[$i]['hor_l1']);
                $hor_l2 = $this->parseMSel($pivot_settings['horizontal']['l2_split'], $res[$i]['hor_l2']);
                $hor_l3 = $this->parseMSel($pivot_settings['horizontal']['l3_split'], $res[$i]['hor_l3']);
                $vert_l1 = $this->parseMSel($pivot_settings['vertical']['l1_split'], $res[$i]['vert_l1']);
                $vert_l2 = $this->parseMSel($pivot_settings['vertical']['l2_split'], $res[$i]['vert_l2']);
                $vert_l3 = $this->parseMSel($pivot_settings['vertical']['l3_split'], $res[$i]['vert_l3']);
                $new_arr = $this->buildRes($new_arr, $hor_l1, $hor_l2, $hor_l3, $vert_l1, $vert_l2, $vert_l3, $res[$i]['y']);
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
     * @param array $hor_l1
     * @param array $hor_l2
     * @param array $hor_l3
     * @param array $vert_l1
     * @param array $vert_l2
     * @param array $vert_l3
     * @param $y
     * @return array
     */
    protected function buildRes(array $new_arr, array $hor_l1, array $hor_l2, array $hor_l3, array $vert_l1, array $vert_l2, array $vert_l3, $y)
    {
        foreach ($hor_l1 as $h1) {
            foreach ($hor_l2 as $h2) {
                foreach ($hor_l3 as $h3) {
                    foreach ($vert_l1 as $v1) {
                        foreach ($vert_l2 as $v2) {
                            foreach ($vert_l3 as $v3) {
                                $new_arr[] = [
                                    'hor_l1' => $h1,
                                    'hor_l2' => $h2,
                                    'hor_l3' => $h3,
                                    'vert_l1' => $v1,
                                    'vert_l2' => $v2,
                                    'vert_l3' => $v3,
                                    'y' => $y,
                                ];
                            }
                        }
                    }
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
        return $this->tableRepository->updateChart($chart->id, $data, $param_name);
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
        if ($ch = $this->tableRepository->getChart($data['id'])) {
            $this->tableRepository->delChart($ch->id);
        }
        return 1;
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
}