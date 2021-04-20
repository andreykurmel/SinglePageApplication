<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartRight;

class TableChartRepository
{

    /**
     * TableChartRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param int $chart_id
     * @param int $table_id
     * @param int $row_idx
     * @param int $col_idx
     * @param array $chart_settings
     * @return mixed
     */
    public function realignChart(int $chart_id, int $table_id, int $row_idx, int $col_idx, array $chart_settings)
    {
        return TableChart::where('id', '=', $chart_id)
            ->where('table_id', '=', $table_id)
            ->update([
                'row_idx' => $row_idx,
                'col_idx' => $col_idx,
                'chart_settings' => json_encode($chart_settings),
            ]);
    }

    /**
     * @param TableChart $chart
     * @param int $table_permis_id
     * @param $can_edit
     * @return mixed
     */
    public function toggleChartRight(TableChart $chart, int $table_permis_id, $can_edit) {
        $right = $chart->_chart_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableChartRight::create([
                'table_chart_id' => $chart->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableChart $chart
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteChartRight(TableChart $chart, int $table_permis_id) {
        return $chart->_chart_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}