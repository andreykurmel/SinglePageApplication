<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionAddRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionCopyRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionItemAddRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionItemDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionItemUpdateRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefConditionUpdateRequest;
use Vanguard\Http\Requests\Tablda\TableRefCondition\TableRefIcomingUpdateRequest;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\DirectDatabase;

class TableRefConditionController extends Controller
{
    private $tableService;
    private $refConditionRepository;

    /**
     * TableRefConditionController constructor.
     *
     * @param TableService $tableService
     * @param TableRefConditionRepository $refConditionRepository
     */
    public function __construct(TableService $tableService, TableRefConditionRepository $refConditionRepository)
    {
        $this->tableService = $tableService;
        $this->refConditionRepository = $refConditionRepository;
    }

    /**
     * Add Referencing Condition
     *
     * @param TableRefConditionAddRequest $request
     * @return mixed
     */
    public function insertRefCondition(TableRefConditionAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->refConditionRepository->addRefCondition(
            array_merge($request->fields, ['table_id' => $request->table_id]),
            $table,
            auth()->id()
        );
    }

    /**
     * Update Referencing Condition
     *
     * @param TableRefConditionUpdateRequest $request
     * @return mixed
     */
    public function updateRefCondition(TableRefConditionUpdateRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefCondition($request->table_ref_condition_id);
        $table = $this->tableService->getTable($ref_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->refConditionRepository->updateRefCondition($ref_cond->id, $request->fields);
    }

    /**
     * Delete Referencing Condition
     *
     * @param TableRefConditionDeleteRequest $request
     * @return mixed
     */
    public function deleteRefCondition(TableRefConditionDeleteRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefCondition($request->table_ref_condition_id);
        $table = $this->tableService->getTable($ref_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->refConditionRepository->deleteRefCondition($ref_cond->id);
    }

    /**
     * Update incoming Link
     *
     * @param TableRefIcomingUpdateRequest $request
     * @return array
     */
    public function updIncomRef(TableRefIcomingUpdateRequest $request) {
        $ref_cond = $this->refConditionRepository->getRefCondition($request->table_ref_condition_id);
        $table = $this->tableService->getTable($ref_cond->ref_table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $new_link = $this->refConditionRepository->updateRefCondition($ref_cond->id, ['incoming_allow' => $request->incoming_allow ? 1 : 0]);
        return [
            '__incoming_links' => DirectDatabase::loadIncomingLinks($table->id)
        ];
    }

    /**
     * Copy Referencing Condition
     *
     * @param TableRefConditionCopyRequest $request
     * @return mixed
     */
    public function copyRefCondition(TableRefConditionCopyRequest $request)
    {
        $from_cond = $this->refConditionRepository->getRefCondition($request->from_rc_id);
        $to_cond = $this->refConditionRepository->getRefCondition($request->to_rc_id);

        $table_from = $this->tableService->getTable($from_cond->table_id);
        $table_to = $this->tableService->getTable($to_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table_from]);
        $this->authorize('isOwner', [TableData::class, $table_to]);

        return $this->refConditionRepository->copyRefCondition($from_cond, $to_cond);
    }

    /**
     * Add Referencing Condition Item
     *
     * @param TableRefConditionItemAddRequest $request
     * @return TableRefCondition
     */
    public function insertRefConditionItem(TableRefConditionItemAddRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefCondition($request->table_ref_condition_id);
        $table = $this->tableService->getTable($ref_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $this->refConditionRepository->addRefConditionItem(
            array_merge($request->fields, ['table_ref_condition_id' => $request->table_ref_condition_id])
        );

        return $this->refConditionRepository->loadRefCondWithRelations($ref_cond->id);
    }

    /**
     * Update Referencing Condition Item
     *
     * @param TableRefConditionItemUpdateRequest $request
     * @return TableRefCondition
     */
    public function updateRefConditionItem(TableRefConditionItemUpdateRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefConditionByItemId($request->table_ref_condition_item_id);
        $table = $this->tableService->getTable($ref_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $this->refConditionRepository->updateRefConditionItem(
            $request->table_ref_condition_item_id,
            array_merge($request->fields, ['table_ref_condition_id' => $ref_cond->id])
        );

        return $this->refConditionRepository->loadRefCondWithRelations($ref_cond->id);
    }

    /**
     * Delete Referencing Condition Item
     *
     * @param TableRefConditionItemDeleteRequest $request
     * @return TableRefCondition
     */
    public function deleteRefConditionItem(TableRefConditionItemDeleteRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefConditionByItemId($request->table_ref_condition_item_id);
        $table = $this->tableService->getTable($ref_cond->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $this->refConditionRepository->deleteRefConditionItem($request->table_ref_condition_item_id);

        return $this->refConditionRepository->loadRefCondWithRelations($ref_cond->id);
    }
}
