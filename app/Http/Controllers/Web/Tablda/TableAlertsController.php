<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableAlertService;

class TableAlertsController extends Controller
{
    private $alertService;
    private $tableRepository;

    /**
     * TableBackupsController constructor.
     * @param TablealertService $alertService
     * @param TableRepository $tableRepository
     */
    public function __construct(TableAlertService $alertService, TableRepository $tableRepository)
    {
        $this->alertService = $alertService;
        $this->tableRepository = $tableRepository;
    }

    /**
     * Add Alert
     *
     * @param Request $request
     * @return mixed
     */
    public function insert(Request $request)
    {
        $table = $this->tableRepository->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->alertService->insertAlert( array_merge($request->fields, ['table_id' => $table->id]) );
    }

    /**
     * Update Alert.
     *
     * @param Request $request
     * @return TableAlert
     */
    public function update(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $table = $table_alert->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->alertService->updateAlert( $table_alert->id, array_merge($request->fields, ['table_id' => $table->id]) );
    }

    /**
     * Delete Alert
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $table = $table_alert->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'status' => $this->alertService->deleteAlert( $table_alert->id )
        ];
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function insertCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $table = $table_alert->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->alertService->insertAlertCond( $table_alert->id, $request->fields );
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function updateCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $table = $table_alert->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->alertService->updateAlertCond( $table_alert->id, $request->table_cond_id, $request->fields );
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function deleteCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $table = $table_alert->_table;

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->alertService->deleteAlertCond( $table_alert->id, $request->table_cond_id );
    }
}
