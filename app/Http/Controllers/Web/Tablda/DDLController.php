<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\DDL\DDLAddRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLCopyFromtableRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLDeleteRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLFillRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLItemAddRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLItemDeleteRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLItemUpdateRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLParseRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLReferenceAddRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLReferenceDeleteRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLReferenceUpdateRequest;
use Vanguard\Http\Requests\Tablda\DDL\DDLUpdateRequest;
use Vanguard\Http\Requests\Tablda\DDL\NewOptionRequest;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\CopyTableRepository;
use Vanguard\Services\Tablda\DDLService;
use Vanguard\Services\Tablda\TableService;

class DDLController extends Controller
{
    private $ddlService;
    private $tableService;

    /**
     * RightController constructor.
     *
     * @param DDLService $ddlService
     * @param TableService $tableService
     */
    public function __construct(DDLService $ddlService, TableService $tableService)
    {
        $this->ddlService = $ddlService;
        $this->tableService = $tableService;
    }

    /**
     * @param DDLAddRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function insertDDL(DDLAddRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->ddlService->addDDL($table, $request->fields);
    }

    /**
     * @param DDLUpdateRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDL(DDLUpdateRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->ddlService->updateDDL($table, $request->ddl_id, $request->fields);
    }

    /**
     * @param DDLDeleteRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deleteDDL(DDLDeleteRequest $request) {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->ddlService->deleteDDL($table, $request->ddl_id);
    }

    /**
     * Fill DDL By Distinctive Values From Field.
     *
     * @param DDLFillRequest $request
     * @return mixed
     */
    public function fillFromField(DDLFillRequest $request) {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        return $this->ddlService->fillDDL($table, $request->table_field_id, $request->ddl_id);
    }

    /**
     * Fill DDL By Options from string.
     *
     * @param DDLParseRequest $request
     * @return mixed
     */
    public function parseOptions(DDLParseRequest $request) {
        $ddl = $this->ddlService->getDDL($request->ddl_id);
        $this->authorize('isOwner', [TableData::class, $ddl ? $ddl->_table : null]);
        return $this->ddlService->parseOptions($request->options, $request->ddl_id);
    }

    /**
     * Fill DDL By Options from string.
     *
     * @param NewOptionRequest $request
     * @return mixed
     */
    public function newOption(NewOptionRequest $request) {
        $ddl = $this->ddlService->getDDL($request->ddl_id);
        $this->authorize('isOwner', [TableData::class, $ddl ? $ddl->_table : null]);
        if ($request->ddl_ref_id > -1) {
            $arr = $this->ddlService->newReferencingOption($ddl, $request->new_val, $request->ddl_ref_id, $request->fields);
        } else {
            $arr = $this->ddlService->newRegularOption($ddl, $request->new_val, $request->extra_options);
        }

        if ($arr['err']) {
            return response('You do not necessary permission(s) for adding new option to the dropdown list (DDL).', 500);
        } else {
            return [
                'items' => $ddl->_items()->get(),
                'row_id' => $arr['val'],
            ];
        }
    }

    /**
     * Add DDL Item
     *
     * @param DDLItemAddRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function insertDDLItem(DDLItemAddRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->addDDLItem($ddl, $request->fields);
    }

    /**
     * Update DDL Item.
     *
     * @param DDLItemUpdateRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDLItem(DDLItemUpdateRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->updateDDLItem($ddl, $request->ddl_item_id, $request->fields);
    }

    /**
     * Delete DDL Item
     *
     * @param DDLItemDeleteRequest $request
     * @return mixed
     */
    public function deleteDDLItem(DDLItemDeleteRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->deleteDDLItem($ddl, $request->ddl_item_id);
    }

    /**
     * @param DDLReferenceAddRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function insertDDLReference(DDLReferenceAddRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->addDDLReference($ddl, $request->fields);
    }

    /**
     * @param DDLReferenceUpdateRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateDDLReference(DDLReferenceUpdateRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->updateDDLReference($ddl, $request->ddl_ref_id, $request->fields);
    }

    /**
     * @param DDLReferenceDeleteRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function deleteDDLReference(DDLReferenceDeleteRequest $request){
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $ddl = $this->ddlService->tableDDL($table, $request->ddl_id);
        return $this->ddlService->deleteDDLReference($ddl, $request->ddl_ref_id);
    }

    /**
     * @param DDLCopyFromtableRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function copyDDLfromTable(DDLCopyFromtableRequest $request){
        $table = $this->tableService->getTable($request->target_table_id);
        $this->authorize('isOwner', [TableData::class, $table]);

        $from_table = $this->tableService->getTable($request->ref_table_id);
        (new CopyTableRepository())->copySingleDDL($from_table, $table, auth()->user(), [$request->ref_ddl_id]);

        return $this->ddlService->returnDDLS($table);
    }
}
