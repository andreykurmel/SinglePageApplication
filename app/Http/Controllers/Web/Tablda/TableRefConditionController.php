<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
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
use Vanguard\Modules\InheritColumnModule;
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
     * @return array
     */
    public function insertRefCondition(TableRefConditionAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $refCond = $this->refConditionRepository->addRefCondition(
            array_merge($request->fields, ['table_id' => $request->table_id]),
            $table
        );

        return [
            'ref_cond' => $refCond,
            '_rcmap_positions' => $table->_rcmap_positions()->get(),
        ];
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
    public function updIncomRef(TableRefIcomingUpdateRequest $request)
    {
        $ref_cond = $this->refConditionRepository->getRefCondition($request->table_ref_condition_id);
        $table = $this->tableService->getTable($ref_cond->ref_table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $new_link = $this->refConditionRepository->updateRefCondition($ref_cond->id, ['incoming_allow' => $request->incoming_allow ? 1 : 0]);
        return [
            '__incoming_links' => DirectDatabase::loadIncomingLinks($table->id)
        ];
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

        $refCond = $this->refConditionRepository->updateRefCondition($ref_cond->id, $request->fields);

        return [
            'ref_cond' => $refCond,
            '_rcmap_positions' => $table->_rcmap_positions()->get(),
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
     * @param Request $request
     * @return TableRefCondition
     * @throws AuthorizationException
     */
    public function addReverseRefCond(Request $request)
    {
        $refCond = $this->refConditionRepository->getRefCondition($request->ref_cond_id);
        $table = $this->tableService->getTable($refCond->ref_table_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->refConditionRepository->addReverseRefCond($refCond);
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

    /**
     * @param GetTableRequest $request
     * @return array
     * @throws AuthorizationException
     */
    public function getIncomingForTable(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return $this->refConditionRepository->loadIncomingRefConds($table->id);
    }

    /**
     * @param GetTableRequest $request
     * @return array
     * @throws AuthorizationException
     */
    public function getInheritedTree(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('load', [TableData::class, $table]);
        return InheritColumnModule::childrenTables([$table->id]);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\DataSetPermissions\TableRcmapPosition
     * @throws AuthorizationException
     */
    public function updateOrCreateRcMaps(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return $this->refConditionRepository->updateOrCreateRcMaps(
            array_merge($request->all(), [
                'table_id' => $table->id,
                'user_id' => auth()->id(),
            ])
        );
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\DataSetPermissions\TableRcmapPosition[]
     * @throws AuthorizationException
     */
    public function deleteRcMaps(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return $this->refConditionRepository->deleteRcMaps($table->id, $request->position);
    }
}
