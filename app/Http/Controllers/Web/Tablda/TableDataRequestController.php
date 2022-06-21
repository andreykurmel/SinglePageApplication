<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
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
     * @return \Illuminate\Support\Collection
     */
    public function copyPermis(TableDcrCopyRequest $request)
    {
        $from_data_request = $this->tableDataRequestRepository->getDataRequest($request->from_data_request_id);
        $to_data_request = $this->tableDataRequestRepository->getDataRequest($request->to_data_request_id);
        $table_from = $this->tableService->getTable($from_data_request->table_id);
        $table_to = $this->tableService->getTable($to_data_request->table_id);

        //$this->authorize('isOwner', [TableData::class, $table_from]);
        $this->authorize('isOwner', [TableData::class, $table_to]);

        $fields = $request->as_template ? (new TableDataRequest())->design_tab : [];
        (new CopyDataRequestRepository())->copyDataRequest($from_data_request, $to_data_request, !!$request->as_template, $fields);
        $table_to->_is_owner = true;
        return $this->tableDataRequestRepository->loadWithRelations($table_to->id, $to_data_request->id)->get();
    }

    /**
     * Add Table DataRequest
     *
     * @param TableDcrAddRequest $request
     * @return mixed
     */
    public function insertTableDataRequest(TableDcrAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $arr = array_merge($request->fields, ['table_id' => $request->table_id]);

        if (!empty($request->fields['user_link']) && $this->tableDataRequestRepository->checkAddress($table->name, $request->fields['user_link'])) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tableDataRequestRepository->addDataRequest($arr);
        }
    }

    /**
     * Update Table DataRequest
     *
     * @param TableDcrUpdateRequest $request
     * @return array
     */
    public function updateTableDataRequest(TableDcrUpdateRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if (!empty($request->fields['user_link']) && $this->tableDataRequestRepository->checkAddress($table->name, $request->fields['user_link'], $data_request->id)) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tableDataRequestRepository->updateDataRequest($data_request->id, $request->fields);
        }
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

        $this->authorize('isOwner', [TableData::class, $table]);

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
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);

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

        $this->authorize('isOwner', [TableData::class, $table]);

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

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tableDataRequestService->defaultField($data_request->id, $request->table_field_id, $request->default_val);
    }

    /**
     * Add DCR File.
     *
     * @param TableDataRequestFileRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addDcrFile(TableDataRequestFileRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return [
            'filepath' => $this->tableDataRequestRepository->insertDCRFile($data_request, $request->field, $request->u_file)
        ];
    }

    /**
     * Delete DCR File.
     *
     * @param TableDataRequestFileRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteDcrFile(TableDataRequestFileRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_data_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['status' => $this->tableDataRequestRepository->deleteDCRFile($data_request, $request->field)];
    }

    /**
     * @param LinkedTableAddRequest $request
     * @return \Vanguard\Models\Dcr\DcrLinkedTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertLinkedTable(LinkedTableAddRequest $request)
    {
        $data_request = $this->tableDataRequestRepository->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tableDataRequestRepository->insertLinkedTable($data_request->id, $request->fields);
    }

    /**
     * @param LinkedTableUpdateRequest $request
     * @return \Vanguard\Models\Dcr\DcrLinkedTable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateLinkedTable(LinkedTableUpdateRequest $request)
    {
        $linked = $this->tableDataRequestRepository->getLinkedTable($request->table_linked_id);
        $data_request = $this->tableDataRequestRepository->getDataRequest($linked->table_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tableDataRequestRepository->updateLinkedTable($linked->id, $request->fields);
    }

    /**
     * @param LinkedTableDirectRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteLinkedTable(LinkedTableDirectRequest $request)
    {
        $linked = $this->tableDataRequestRepository->getLinkedTable($request->table_linked_id);
        $data_request = $this->tableDataRequestRepository->getDataRequest($linked->table_request_id);
        $table = $this->tableService->getTable($data_request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['res' => $this->tableDataRequestRepository->deleteLinkedTable($linked->id)];
    }
}
