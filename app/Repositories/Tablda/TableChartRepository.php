<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartRight;
use Vanguard\Services\Tablda\HelperService;

class TableChartRepository
{
    protected $service;

    /**
     * TableChartRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Chart By Id.
     *
     * @param int $id
     * @return TableChart
     */
    public function getChart($id)
    {
        return TableChart::where('id', $id)->first();
    }

    /**
     * Create Table Chart.
     *
     * @param array $data
     * @return mixed
     */
    public function createChart(array $data)
    {
        $ch = TableChart::create($this->service->delSystemFields($data));
        $set = json_decode($ch->chart_settings, true);
        $set['id'] = $ch->id;
        $ch->update(['chart_settings' => json_encode($set)]);
        return $ch;
    }

    /**
     * Update Table Chart.
     *
     * @param int $chart_id
     * @param array $data
     * @param string $param_name
     * @return mixed
     */
    public function updateChart(int $chart_id, array $data, string $param_name)
    {
        if ($param_name != '__update_cache') {
            TableChart::where('id', '=', $chart_id)
                ->update($this->service->delSystemFields($data));
        }

        return TableChart::where('id', '=', $chart_id)->first();
    }

    /**
     * @param int $chart_id
     * @param array $settings
     * @return bool
     */
    public function updateChartSettings(int $chart_id, array $settings)
    {
        $ch = $this->getChart($chart_id);
        $new_settings = array_merge(json_decode($ch->chart_settings, true), $settings);
        $new_settings['id'] = $ch->id;
        return $ch->update(['chart_settings' => json_encode($new_settings)]);
    }

    /**
     * Delete Table Chart.
     *
     * @param int $chart_id
     * @return mixed
     */
    public function delChart(int $chart_id)
    {
        return TableChart::where('id', $chart_id)->delete();
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