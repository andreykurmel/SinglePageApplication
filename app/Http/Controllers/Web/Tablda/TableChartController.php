<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableData\RealignChartsRequest;
use Vanguard\Http\Requests\Tablda\TableData\SaveChartRequest;
use Vanguard\Http\Requests\Tablda\TableData\ToggleChartRightRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\ChartService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableChartController extends Controller
{

    protected $tableService;
    protected $chartService;
    protected $notNeededData = ['dimensions','pivot.data_widths','pivot.vert_widths'];//optimization of settings which are not change dataset

    /**
     * TableDataController constructor.
     * @param ChartService $chartService
     * @param TableService $tableService
     */
    public function __construct(
        ChartService $chartService,
        TableService $tableService
    )
    {
        $this->chartService = $chartService;
        $this->tableService = $tableService;
    }

    /**
     * Save Charts.
     *
     * @param SaveChartRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function saveChart(SaveChartRequest $request): array
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $all_settings = $request->all_settings;

        $data = [
            'id' => $request->id,
            'table_chart_tab_id' => $request->table_chart_tab_id,
            'table_id' => $table->id,
            'user_id' => auth()->id(),
            'row_idx' => $request->row_idx,
            'col_idx' => $request->col_idx,
            'name' => $request->name ?: '',
            'title' => $request->title ?: '',
            'chart_settings' => json_encode($all_settings),
        ];

        if ($request->should_del) {
            $this->chartService->delChart($data);
            return [];
        } else {
            $ch = $this->chartService->getChart($data);

            try {
                if (!in_array($request->changed_param, $this->notNeededData)) {
                    $chSettings = $this->chartService->prepareChartSettings($ch, $all_settings);
                    [$chart_data, $table_data] = $this->chartService->getChartAndTableData($table, $chSettings, $request->request_params);
                    if ($chSettings && $chSettings['elem_type'] == 'pivot_table' && $chSettings['pivot_table']) {
                        $a_len = $chSettings['pivot_table']['len_about'] ?? 1;
                        if ($a_len > 1) {
                            [$chart_data_2, $table_data_2] = $this->chartService->getChartAndTableData($table, $chSettings, $request->request_params, 2);
                        }
                        if ($a_len > 2) {
                            [$chart_data_3, $table_data_3] = $this->chartService->getChartAndTableData($table, $chSettings, $request->request_params, 3);
                        }
                        if ($a_len > 3) {
                            [$chart_data_4, $table_data_4] = $this->chartService->getChartAndTableData($table, $chSettings, $request->request_params, 4);
                        }
                        if ($a_len > 4) {
                            [$chart_data_5, $table_data_5] = $this->chartService->getChartAndTableData($table, $chSettings, $request->request_params, 5);
                        }
                    }
                }
            } catch (\Exception $e) {
                $data_receiving_error = 1;
            }

            $data['user_id'] = $ch->user_id;
            $ch = $this->chartService->saveChart($ch, $data, $request->changed_param);
            $ch->load('_chart_rights');

            $chart_ar = $ch->toArray();
            $chart_ar['chart_settings'] = json_decode($ch->chart_settings);
            $chart_ar['cached_data'] = json_decode($ch->cached_data);

            return [
                'id' => $ch->id,
                'chart_data' => $chart_data ?? null,
                'table_data' => $table_data ?? null,
                'table_data_2' => $table_data_2 ?? null,
                'table_data_3' => $table_data_3 ?? null,
                'table_data_4' => $table_data_4 ?? null,
                'table_data_5' => $table_data_5 ?? null,
                'chart_obj' => $chart_ar,
                'data_receiving_error' => $data_receiving_error ?? 0,
            ];
        }
    }

    /**
     * @param RealignChartsRequest $request
     * @return array
     */
    public function realignCharts(RealignChartsRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        return ['status' => $this->chartService->realignCharts($table, $request->charts)];
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportChart(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $target_table = $this->tableService->getTable($request->target_table_id);
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $target_table]);

        $chid = $request->chart_settings ? $request->chart_settings['id'] : '';
        $chart = $this->chartService->getChart(['id' => $chid]);
        if ($chart->user_id == auth()->id()) {
            $chSettings = $this->chartService->prepareChartSettings($chart, $request->chart_settings);
            return $this->chartService->exportChart($chart->_table, $chSettings, $target_table, $request->request_params ?? []);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function saveChartSettings(Request $request)
    {
        $chart = $this->chartService->getChart(['id' => $request->chart_id]);
        if ($chart->user_id == auth()->id()) {
            return $this->chartService->updateChartSettings($chart, $request->chart_settings);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param ToggleChartRightRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function toggleChartRight(ToggleChartRightRequest $request) {
        $chart = $this->chartService->getChart(['id' => $request->chart_id]);
        if ($chart->user_id == auth()->id()) {
            return $this->chartService->toggleChartRight($chart, $request->permission_id, $request->can_edit);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param ToggleChartRightRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function delChartRight(ToggleChartRightRequest $request) {
        $chart = $this->chartService->getChart(['id' => $request->chart_id]);
        if ($chart->user_id == auth()->id()) {
            return $this->chartService->deleteChartRight($chart, $request->permission_id);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertTab(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->chartService->insertTab(array_merge($request->fields, ['table_id' => $table->id]));
        return $table->_chart_tabs;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateTab(Request $request)
    {
        $chartTab = $this->chartService->getTab($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $chartTab->_table;
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->chartService->updateTab($request->model_id, array_merge($request->fields, ['table_id' => $table->id]));
        return $table->_chart_tabs;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteTab(Request $request)
    {
        $chartTab = $this->chartService->getTab($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $chartTab->_table;
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->chartService->deleteTab($request->model_id);
        return $table->_chart_tabs;
    }
}
