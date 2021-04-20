<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Request;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\ShowColumnsToggleRequest;
use Vanguard\Http\Requests\Tablda\Table\ImportTooltipsRequest;
use Vanguard\Http\Requests\Tablda\TableData\ChangeOrderColumnRequest;
use Vanguard\Http\Requests\Tablda\TableData\ChangeRowOrderRequest;
use Vanguard\Http\Requests\Tablda\UpdateSettingsRowRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableKanbanRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableFieldService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class SettingsTableController extends Controller
{

    private $tableDataService;
    private $tableService;
    private $fieldService;

    /**
     * SettingsTableController constructor.
     *
     * @param TableDataService $tableDataService
     * @param TableService $tableService
     * @param TableFieldService $fieldService
     */
    public function __construct(
        TableDataService $tableDataService,
        TableService $tableService,
        TableFieldService $fieldService
    )
    {
        $this->tableDataService = $tableDataService;
        $this->tableService = $tableService;
        $this->fieldService = $fieldService;
    }

    /**
     * Get table fields info
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function getFieldsForTable(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->fieldService->getFieldsForTable($request->table_id);
    }

    /**
     * Get Fees table (Plans and Addons prices)
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->tableService->getSettingsFees();
    }

    /**
     * Update columns settings for user
     *
     * @param UpdateSettingsRowRequest $request
     * @return array
     */
    public function updateSettingsRow(UpdateSettingsRowRequest $request)
    {
        if (auth()->id()) {
            return ['fld' => $this->fieldService->updateSettingsRow($request->table_field_id, auth()->id(), $request->field, $request->val, $request->recalc_ids ?: [])];
        }
        return ['fld' => null];
    }

    /**
     * Update all columns settings for user
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function updateMassSettings(\Illuminate\Http\Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($user->id == $table->user_id) {
            return ['fld' => $this->fieldService->massSettingsUpdate($request->table_id, $request->field, $request->val)];
        }
        return ['fld' => null];
    }

    /**
     * Show Hide Column Toggle
     *
     * @param ShowColumnsToggleRequest $request
     * @return bool
     */
    public function showColumnsToggle(ShowColumnsToggleRequest $request)
    {
        $fields = $this->fieldService->getField($request->table_field_ids);
        $tb_id = collect($fields)->pluck('table_id')->unique();
        if ($tb_id->count() > 1) {
            abort(403);
        }
        $table = $this->tableService->getTable($tb_id->first());
        $this->authorize('load', [TableData::class, $table]);
        return $this->tableDataService->showColumnsToggle(auth()->id(), $request->table_field_ids, $request->is_showed);
    }

    /**
     * Change Order Column
     *
     * @param ChangeOrderColumnRequest $request
     * @return array|\Vanguard\Models\Table\Table
     */
    public function changeOrderColumn(ChangeOrderColumnRequest $request)
    {
        if (auth()->id()) {
            $table = $this->tableService->getTable( $request->table_id );
            $this->authorize('load', [TableData::class, $table]);
            $this->tableDataService->changeOrderColumn($request->all());
            return $this->tableService->getWithFields($request->table_id, auth()->id());
        }
        return ['status' => 0];
    }

    /**
     * Change Order Column
     *
     * @param ChangeRowOrderRequest $request
     * @return array
     */
    public function changeRowOrder(ChangeRowOrderRequest $request)
    {
        if (auth()->id()) {
            $table = $this->tableService->getTable($request->table_id);
            $this->authorize('load', [TableData::class, $table]);
            return $this->tableDataService->changeRowOrder($table, $request->from_order, $request->to_order, $request->from_id, $request->to_id);
        }
        return ['status' => 0];
    }

    /**
     * Parse string and import Tooltips to TableFields.
     *
     * @param ImportTooltipsRequest $request
     * @return mixed
     */
    public function importTooltips(ImportTooltipsRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return $this->tableService->parseTooltips($table, auth()->id(), $request->options);
    }

    /**
     * Parse string and import Tooltips to TableFields.
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function loadCondFormats(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('load', [TableData::class, $table]);
        (new TableRepository())->loadCondFormats($table, auth()->id());

        return $table->_cond_formats;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function updateKanban(\Illuminate\Http\Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($request->kanban_id && $request->field) {
            $repo = new TableKanbanRepository();
            $repo->updateKanban($request->kanban_id, $request->field, $request->val);
            return $repo->getKanban($request->kanban_id);
        }
        return [];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function attachDetachKanbanFld(\Illuminate\Http\Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($request->kanban_id && $request->field_id) {
            $repo = new TableKanbanRepository();
            $repo->attachDetachFld($request->kanban_id, $request->field_id, !!$request->val);
            return $repo->getKanban($request->kanban_id);
        }
        return [];
    }
}
