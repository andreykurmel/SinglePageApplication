<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TablePermission\LinkedTableAddRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\LinkedTableDirectRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\LinkedTableUpdateRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDataRequestFileRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrAddRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrCopyRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrDefaultFieldRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrDirectRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrUpdateColumnGroupsRequest;
use Vanguard\Http\Requests\Tablda\TablePermission\TableDcrUpdateRequest;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\CopyDataRequestRepository;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
use Vanguard\Services\Tablda\Permissions\TableDataRequestService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableDataRequestController extends Controller
{
    use CanEditAddon;

    private $tableService;
    private $tableDataRequestRepository;
    private $tableDataRequestService;
    private $tableDataService;
    private $rowGroupRepository;

    /**
     * @param TableService $tableService
     * @param TableDataRequestRepository $tableDataRequestRepository
     * @param TableDataRequestService $tableDataRequestService
     * @param TableDataService $tableDataService
     */
    public function __construct(
        TableService               $tableService,
        TableDataRequestRepository $tableDataRequestRepository,
        TableDataRequestService    $tableDataRequestService,
        TableDataService           $tableDataService
    )
    {
        $this->tableService = $tableService;
        $this->tableDataRequestRepository = $tableDataRequestRepository;
        $this->tableDataRequestService = $tableDataRequestService;
        $this->tableDataService = $tableDataService;
    }

    /**
     * @param TableDcrDirectRequest $request
     * @return mixed
     */
    public function checkPermis(TableDcrDirectRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($data_request->table_id);

        //$this->authorize('isOwner', [TableData::class, $table]);

        return $data_request;
    }

    /**
     * @param TableDcrCopyRequest $request
     * @return Collection
     * @throws AuthorizationException
     */
    public function copyPermis(TableDcrCopyRequest $request)
    {
        $from_data_request = $this->tableDataRequestRepository->getDataRequest($request->from_data_request_id);
        $to_data_request = $this->tableDataRequestRepository->getDataRequest($request->to_data_request_id);
        $table_from = $this->tableService->getTable($from_data_request->table_id);
        $table_to = $this->tableService->getTable($to_data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table_to]);

        $fields = $request->full_copy ? [] : (new TableDataRequest())->design_tab;
        (new CopyDataRequestRepository())->copyDataRequest($from_data_request, $to_data_request, $fields, !!$request->full_copy);
        $table_to->_is_owner = true;
        return $this->tableDataRequestRepository->loadWithRelations($table_to->id, $to_data_request->id);
    }

    /**
     * @param TableDcrAddRequest $request
     * @return ResponseFactory|Application|Response|mixed
     */
    public function insertTableDataRequest(TableDcrAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->canEditAddon($table, 'request');

        $arr = array_merge($request->fields, ['table_id' => $request->table_id]);

        if ($this->tableDataRequestRepository->checkAddress($request->fields['custom_url'] ?? '')) {
            return response('This Custom URL is already used! Enter a different one.', 400);
        }

        return $this->tableDataRequestRepository->addDataRequest($arr);
    }

    /**
     * @param TableDcrUpdateRequest $request
     * @return ResponseFactory|Application|Response|TableDataRequest
     */
    public function updateTableDataRequest(TableDcrUpdateRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        if ($this->tableDataRequestRepository->checkAddress($request->fields['custom_url'] ?? '', $data_request->id)) {
            return response('This Custom URL is already used! Enter a different one.', 400);
        }

        return $this->tableDataRequestRepository->updateDataRequest($data_request->id, $request->fields);
    }

    /**
     * Delete Table DataRequest
     *
     * @param TableDcrDirectRequest $request
     * @return mixed
     */
    public function deleteTableDataRequest(TableDcrDirectRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return $this->tableDataRequestRepository->deleteDataRequest($data_request->id, $data_request->table_id);
    }

    /**
     * Check pass for Table DataRequest
     *
     * @param TableDcrDirectRequest $request
     * @return mixed
     */
    public function checkPass(TableDcrDirectRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);

        return ['status' => $data_request->pass == $request->pass];
    }

    /**
     * @param Request $request
     * @return bool[]
     */
    public function requestRowPass(Request $request)
    {
        $user = auth()->user() ?: new User();
        $dcr = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($dcr->table_id);
        $row = $this->tableDataService->getDirectRow($table, $request->dcr_row_id);
        return ['status' => $dcr->_dcr_pass_field && $row[$dcr->_dcr_pass_field->field] == $request->pass];
    }

    /**
     * Update Column in Table DataRequest
     *
     * @param TableDcrUpdateColumnGroupsRequest $request
     * @return mixed
     */
    public function updateColumnInTableDataRequest(TableDcrUpdateColumnGroupsRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return $this->tableDataRequestRepository->updateTableColDataRequest(
            $request->table_data_request_id,
            $request->table_column_group_id,
            $request->view,
            $request->edit
        );
    }

    /**
     * Change Default Value for Field for provided Table DataRequest.
     *
     * @param TableDcrDefaultFieldRequest $request
     * @return mixed
     */
    public function defaultField(TableDcrDefaultFieldRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return $this->tableDataRequestService->defaultField($data_request->id, $request->table_field_id, $request->default_val);
    }

    /**
     * Add DCR File.
     *
     * @param TableDataRequestFileRequest $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function addDcrFile(TableDataRequestFileRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return [
            'filepath' => $this->tableDataRequestRepository->insertDCRFile($data_request, $request->field, $request->u_file)
        ];
    }

    /**
     * Delete DCR File.
     *
     * @param TableDataRequestFileRequest $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function deleteDcrFile(TableDataRequestFileRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return ['status' => $this->tableDataRequestRepository->deleteDCRFile($data_request, $request->field)];
    }

    /**
     * @param LinkedTableAddRequest $request
     * @return DcrLinkedTable
     * @throws AuthorizationException
     */
    public function insertLinkedTable(LinkedTableAddRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return $this->tableDataRequestRepository->insertLinkedTable($data_request->id, $request->fields);
    }

    /**
     * @param LinkedTableUpdateRequest $request
     * @return DcrLinkedTable
     * @throws AuthorizationException
     */
    public function updateLinkedTable(LinkedTableUpdateRequest $request)
    {
        $linked = $this->tableDataRequestRepository->getLinkedTable($request->table_linked_id);
        $data_request = $this->tableDataRequestRepository->getDataRequest($linked->table_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return $this->tableDataRequestRepository->updateLinkedTable($linked->id, $request->fields);
    }

    /**
     * @param LinkedTableDirectRequest $request
     * @return array
     * @throws AuthorizationException
     */
    public function deleteLinkedTable(LinkedTableDirectRequest $request)
    {
        $linked = $this->tableDataRequestRepository->getLinkedTable($request->table_linked_id);
        $data_request = $this->tableDataRequestRepository->getDataRequest($linked->table_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->canEditAddonItem($table, $data_request->_dcr_rights());

        return ['res' => $this->tableDataRequestRepository->deleteLinkedTable($linked->id)];
    }

    /**
     * @param Request $request
     * @return array|Model
     * @throws AuthorizationException
     */
    public function attachDetachDcrFld(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->canEditAddon($table, 'request');
        if ($request->dcr_id && $request->field_id) {
            $this->tableDataRequestRepository->attachIfNeeded($request->dcr_id, $request->field_id);
            $this->tableDataRequestRepository->changePivotFld($request->dcr_id, $request->field_id, $request->setting, $request->val);
            return $this->tableDataRequestRepository
                ->loadWithRelations($table->id, $request->dcr_id)
                ->first();
        }
        return [];
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function fillDcrUrl(Request $request)
    {
        $dcr = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $this->canEditAddon($dcr->_table, 'request');

        $this->tableDataRequestRepository->fillDcrUrl($dcr);
        return 'done';
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function toggleDcrRight(Request $request)
    {
        $dcr = $this->tableDataRequestRepository->getDataRequest($request->dcr_id);
        $this->authorize('isOwner', [TableData::class, $dcr->_table]);
        return $this->tableDataRequestRepository->toggleDcrRight($dcr, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delDcrRight(Request $request)
    {
        $dcr = $this->tableDataRequestRepository->getDataRequest($request->dcr_id);
        $this->authorize('isOwner', [TableData::class, $dcr->_table]);
        return $this->tableDataRequestRepository->deleteDcrRight($dcr, $request->permission_id);
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function insertNotifLinkedTable(Request $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->dcr_id);
        $this->canEditAddon($data_request->_table, 'request');
        $this->tableDataRequestRepository->insertNotifLinkedTable($data_request->id, $request->fields);
        return $data_request->{$request->type}()->get();
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function updateNotifLinkedTable(Request $request)
    {
        $linked = $this->tableDataRequestRepository->getNotifLinkedTable($request->id);
        $this->canEditAddon($linked->_dcr->_table, 'request');
        $this->tableDataRequestRepository->updateNotifLinkedTable($linked->id, $request->fields);
        return $linked->_dcr->{$request->type}()->get();
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function deleteNotifLinkedTable(Request $request)
    {
        $linked = $this->tableDataRequestRepository->getNotifLinkedTable($request->id);
        $this->canEditAddon($linked->_dcr->_table, 'request');
        $this->tableDataRequestRepository->deleteNotifLinkedTable($linked->id);
        return $linked->_dcr->{$request->type}()->get();
    }
}
