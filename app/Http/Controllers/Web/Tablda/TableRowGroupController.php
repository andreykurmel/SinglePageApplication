<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupAddConditionRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupAddMassRegularRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupAddRegularRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupAddRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupDeleteConditionRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupDeleteRegularRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupUpdateConditionRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupUpdateRegularRequest;
use Vanguard\Http\Requests\Tablda\TableRowGroup\TableRowGroupUpdateRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\TableRowGroupRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

class TableRowGroupController extends Controller
{
    private $tableService;
    private $rowGroupRepository;

    /**
     * TableRowGroupController constructor.
     * 
     * @param TableService $tableService
     * @param TableRowGroupRepository $rowGroupRepository
     */
    public function __construct(TableService $tableService, TableRowGroupRepository $rowGroupRepository)
    {
        $this->tableService = $tableService;
        $this->rowGroupRepository = $rowGroupRepository;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function countRowGroup(Request $request)
    {
        if ($request->table_row_group_id) {
            $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
            $table = $this->tableService->getTable($row_group->table_id);
            $tot = (new TableDataService())->countRowGroupRows($table, $row_group->id);
        } else {
            $tot = 0;
        }
        return [
            'total' => $tot,
        ];
    }

    /**
     * Add Row Group
     *
     * @param TableRowGroupAddRequest $request
     * @return mixed
     */
    public function insertRowGroup(TableRowGroupAddRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->addRowGroup(
            array_merge($request->fields, ['table_id' => $request->table_id])
        );
    }

    /**
     * Update Row Group
     *
     * @param TableRowGroupUpdateRequest $request
     * @return array
     */
    public function updateRowGroup(TableRowGroupUpdateRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->updateRowGroup($row_group->id, $request->fields);
    }

    /**
     * Delete Row Group
     *
     * @param TableRowGroupDeleteRequest $request
     * @return mixed
     */
    public function deleteRowGroup(TableRowGroupDeleteRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->deleteRowGroup($row_group->id);
    }

    /**
     * Add Row Group Condition
     *
     * @param TableRowGroupAddConditionRequest $request
     * @return mixed
     */
    public function insertRowGroupCondition(TableRowGroupAddConditionRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->addRowGroupCondition(
            array_merge($request->fields, ['table_row_group_id' => $request->table_row_group_id])
        );
    }

    /**
     * Update Row Group Condition
     *
     * @param TableRowGroupUpdateConditionRequest $request
     * @return mixed
     */
    public function updateRowGroupCondition(TableRowGroupUpdateConditionRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroupByCondId($request->table_row_group_condition_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->updateRowGroupCondition(
            $request->table_row_group_condition_id,
            array_merge($request->fields, ['table_row_group_id' => $row_group->id])
        );
    }

    /**
     * Delete Row Group Condition
     *
     * @param TableRowGroupDeleteConditionRequest $request
     * @return mixed
     */
    public function deleteRowGroupCondition(TableRowGroupDeleteConditionRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroupByCondId($request->table_row_group_condition_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->deleteRowGroupCondition($request->table_row_group_condition_id);
    }

    /**
     * Add Row Group Regular
     *
     * @param TableRowGroupAddMassRegularRequest $request
     * @return mixed
     */
    public function insertRowGroupRegularMass(TableRowGroupAddMassRegularRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ($request->rows_ids
            ? $this->rowGroupRepository->addRowGroupRegularMass($table, $request->table_row_group_id, $request->rows_ids)
            : $this->rowGroupRepository->addRowGroupAllToRegular($table, $request->table_row_group_id, $request->request_params, auth()->id()) );
    }

    /**
     * Add Row Group Regular
     *
     * @param TableRowGroupAddRegularRequest $request
     * @return mixed
     */
    public function insertRowGroupRegular(TableRowGroupAddRegularRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroup($request->table_row_group_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->addRowGroupRegular(
            array_merge($request->fields, ['table_row_group_id' => $request->table_row_group_id])
        );
    }

    /**
     * Update Row Group Regular
     *
     * @param TableRowGroupUpdateRegularRequest $request
     * @return mixed
     */
    public function updateRowGroupRegular(TableRowGroupUpdateRegularRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroupByRegId($request->table_row_group_regular_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->updateRowGroupRegular(
            $request->table_row_group_regular_id,
            array_merge($request->fields, ['table_row_group_id' => $row_group->id])
        );
    }

    /**
     * Delete Row Group Regular
     *
     * @param TableRowGroupDeleteRegularRequest $request
     * @return mixed
     */
    public function deleteRowGroupRegular(TableRowGroupDeleteRegularRequest $request){
        $row_group = $this->rowGroupRepository->getRowGroupByRegId($request->table_row_group_regular_id);
        $table = $this->tableService->getTable($row_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->rowGroupRepository->deleteRowGroupRegular($request->table_row_group_regular_id);
    }
}
