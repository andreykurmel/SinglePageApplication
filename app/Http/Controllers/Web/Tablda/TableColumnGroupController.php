<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\TableColGroup\TableColGroupAddRequest;
use Vanguard\Http\Requests\Tablda\TableColGroup\TableColGroupDeleteRequest;
use Vanguard\Http\Requests\Tablda\TableColGroup\TableColGroupUpdateRequest;
use Vanguard\Http\Requests\Tablda\TableColGroup\TableColGroups2TableFieldsRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\TableService;

class TableColumnGroupController extends Controller
{
    private $tableService;
    private $colGroupRepository;
    private $fieldRepository;

    /**
     * TableColGroupController constructor.
     * 
     * @param TableService $tableService
     * @param TableColGroupRepository $colGroupRepository
     */
    public function __construct(TableService $tableService, TableColGroupRepository $colGroupRepository, TableFieldRepository $fieldRepository)
    {
        $this->tableService = $tableService;
        $this->colGroupRepository = $colGroupRepository;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * Add Col Group
     *
     * @param TableColGroupAddRequest $request
     * @return mixed
     */
    public function insertColGroup(TableColGroupAddRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->colGroupRepository->addColGroup(
            array_merge($request->fields, ['table_id' => $request->table_id])
        );
    }

    /**
     * Update Col Group
     *
     * @param TableColGroupUpdateRequest $request
     * @return array
     */
    public function updateColGroup(TableColGroupUpdateRequest $request){
        $col_group = $this->colGroupRepository->getColGroup($request->table_column_group_id);
        $table = $this->tableService->getTable($col_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->colGroupRepository->updateColGroup($col_group->id, $request->fields);
    }

    /**
     * Delete Col Group
     *
     * @param TableColGroupDeleteRequest $request
     * @return mixed
     */
    public function deleteColGroup(TableColGroupDeleteRequest $request){
        $col_group = $this->colGroupRepository->getColGroup($request->table_column_group_id);
        $table = $this->tableService->getTable($col_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->colGroupRepository->deleteColGroup($col_group->id);
    }

    /**
     * Add Field to Col Group
     *
     * @param TableColGroups2TableFieldsRequest $request
     * @return mixed
     */
    public function addFieldToColGroup(TableColGroups2TableFieldsRequest $request){
        $col_group = $this->colGroupRepository->getColGroup($request->table_column_group_id);
        $table = $this->tableService->getTable($col_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        //only not present ids
        $table_field_ids = array_filter($request->table_field_ids, function ($id) use ($col_group) {
            return !$col_group->_fields->where('id', '=', $id)->count();
        });

        $this->colGroupRepository->addColFieldToGroup($col_group, $table_field_ids);
        return $this->fieldRepository->getField($table_field_ids);
    }

    /**
     * Delete Field from Col Group
     *
     * @param TableColGroups2TableFieldsRequest $request
     * @return mixed
     */
    public function deleteFieldFromColGroup(TableColGroups2TableFieldsRequest $request){
        $col_group = $this->colGroupRepository->getColGroup($request->table_column_group_id);
        $table = $this->tableService->getTable($col_group->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->colGroupRepository->deleteColFieldFromGroup($col_group, $request->table_field_ids);
    }
}
