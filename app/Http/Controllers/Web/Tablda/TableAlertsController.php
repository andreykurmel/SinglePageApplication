<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableData\ToggleAlertRightRequest;
use Vanguard\Jobs\AlertAutomationAddJob;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableData;
use Vanguard\Policies\TableAlertPolicy;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\AlertFunctionsService;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\User;

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
     * @param TableAlert $alert
     * @param string $type
     */
    protected function authAlert(TableAlert $alert, string $type)
    {
        $user = auth()->user() ?: new User();
        if (!(new TableAlertPolicy())->$type($user, $alert)) {
            abort(403, 'Forbidden');
        }
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
        return $this->alertService->insertAlert(array_merge($request->fields, ['table_id' => $table->id]));
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
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->updateAlert($table_alert->id, array_merge($request->fields, ['table_id' => $table->id]));
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
        $this->authAlert($table_alert, 'edit');
        return [
            'status' => $this->alertService->deleteAlert($table_alert->id)
        ];
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function insertCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->insertAlertCond($table_alert->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function updateCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->updateAlertCond($table_alert->id, $request->table_cond_id, $request->fields);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function deleteCond(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->deleteAlertCond($table_alert->id, $request->table_cond_id);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function insertAnrTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->insertAnrTable($table_alert->id, $request->fields, $table_alert->_table->id);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function updateAnrTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $user = auth()->user() ?: new User();
        $fields = !(new TableAlertPolicy())->edit($user, $table_alert)
            ? collect($request->fields)->only('temp_is_active')
            : collect($request->fields);
        return $this->alertService->updateAnrTable($table_alert->id, $request->id, $fields->toArray());
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function deleteAnrTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->deleteAnrTable($table_alert->id, $request->id);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function copyAnrFields(Request $request)
    {
        $anr = $this->alertService->getAnrTable($request->to_anr_table_id);
        $this->authAlert($anr->_alert, 'edit');
        return $this->alertService->copyAnrFields($anr->id, $request->from_anr_table_id);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function insertAnrField(Request $request)
    {
        $anr = $this->alertService->getAnrTable($request->anr_table_id);
        $this->authAlert($anr->_alert, 'edit');
        return $this->alertService->insertAnrField($anr->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function updateAnrField(Request $request)
    {
        $anr = $this->alertService->getAnrTable($request->anr_table_id);
        $this->authAlert($anr->_alert, 'edit');
        return $this->alertService->updateAnrField($anr->id, $request->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function deleteAnrField(Request $request)
    {
        $anr = $this->alertService->getAnrTable($request->anr_table_id);
        $this->authAlert($anr->_alert, 'edit');
        return $this->alertService->deleteAnrField($anr->id, $request->id);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function insertUfvTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->insertUfvTable($table_alert->table_id, $table_alert->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function updateUfvTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $user = auth()->user() ?: new User();
        $fields = !(new TableAlertPolicy())->edit($user, $table_alert)
            ? collect($request->fields)->only('temp_is_active')
            : collect($request->fields);
        return $this->alertService->updateUfvTable($table_alert->id, $request->id, $fields->toArray());
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function deleteUfvTable(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->table_alert_id);
        $this->authAlert($table_alert, 'edit');
        return $this->alertService->deleteUfvTable($table_alert->id, $request->id);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function copyUfvFields(Request $request)
    {
        $ufv = $this->alertService->getUfvTable($request->to_ufv_table_id);
        $this->authAlert($ufv->_alert, 'edit');
        return $this->alertService->copyUfvFields($ufv->id, $request->from_ufv_table_id);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function insertUfvField(Request $request)
    {
        $ufv = $this->alertService->getUfvTable($request->ufv_table_id);
        $this->authAlert($ufv->_alert, 'edit');
        return $this->alertService->insertUfvField($ufv->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function updateUfvField(Request $request)
    {
        $ufv = $this->alertService->getUfvTable($request->ufv_table_id);
        $this->authAlert($ufv->_alert, 'edit');
        return $this->alertService->updateUfvField($ufv->id, $request->id, $request->fields);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function deleteUfvField(Request $request)
    {
        $ufv = $this->alertService->getUfvTable($request->ufv_table_id);
        $this->authAlert($ufv->_alert, 'edit');
        return $this->alertService->deleteUfvField($ufv->id, $request->id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function proceedANR(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->alert_id);
        $this->authAlert($table_alert, 'view');
        if ($table_alert) {
            AlertAutomationAddJob::dispatch($table_alert->id);
        } else {
            throw new \Exception('Incorrect `alert id`!', 1);
        }
        return ['success' => 1];
    }

    /**
     * @param Request $request
     * @return TableAlert
     */
    public function storeAnrChanges(Request $request)
    {
        $table_alert = $this->alertService->getAlert($request->alert_id);
        $this->authAlert($table_alert, 'edit');

        (new AlertFunctionsService())->saveChangedTmpAnrs($table_alert);
        return $this->alertService->getAlert($table_alert->id);
    }

    /**
     * @param ToggleAlertRightRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function toggleAlertRight(ToggleAlertRightRequest $request)
    {
        $alert = $this->alertService->getAlert(['id' => $request->alert_id]);
        $this->authAlert($alert, 'owner');
        return $this->alertService->toggleAlertRight($alert, $request->permission_id, $request->can_edit, $request->can_activate);
    }

    /**
     * @param ToggleAlertRightRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public function delAlertRight(ToggleAlertRightRequest $request)
    {
        $alert = $this->alertService->getAlert(['id' => $request->alert_id]);
        $this->authAlert($alert, 'owner');
        return $this->alertService->deleteAlertRight($alert, $request->permission_id);
    }
}
