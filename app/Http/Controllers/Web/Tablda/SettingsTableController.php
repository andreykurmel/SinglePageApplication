<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\ShowColumnsToggleRequest;
use Vanguard\Http\Requests\Tablda\Table\ImportTooltipsRequest;
use Vanguard\Http\Requests\Tablda\TableData\ChangeOrderColumnRequest;
use Vanguard\Http\Requests\Tablda\TableData\ChangeRowOrderRequest;
use Vanguard\Http\Requests\Tablda\TableData\ToggleKanbanRightRequest;
use Vanguard\Http\Requests\Tablda\UpdateSettingsRowRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\TableKanbanRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\ImportService;
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
        TableDataService  $tableDataService,
        TableService      $tableService,
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
            return ['fld' => $this->fieldService->updateSettingsRow($request->table_field_id, auth()->id(), $request->field, $request->val)];
        }
        return ['fld' => null];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function justUserSetts(Request $request)
    {
        if (auth()->id() && $request->table_id && is_array($request->datas)) {
            return ['_cur_settings' => $this->fieldService->justUserSetts($request->table_id, $request->datas)];
        }
        abort(403);
    }

    /**
     * Update all columns settings for user
     *
     * @param Request $request
     * @return array
     */
    public function updateMassSettings(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($user->id == $table->user_id) {
            return ['fld' => $this->fieldService->massSettingsUpdate($request->table_id, $request->field, $request->val, $request->limit_fields ?: [])];
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
     * @return array|Table
     */
    public function changeOrderColumn(ChangeOrderColumnRequest $request)
    {
        if (auth()->id()) {
            $table = $this->tableService->getTable($request->table_id);
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
            if (!$table->is_system) {
                $this->authorize('load', [TableData::class, $table]);
            }
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
     * @param GetTableRequest $request
     * @return \Illuminate\Support\Collection|\Vanguard\Models\DataSetPermissions\CondFormat[]
     * @throws AuthorizationException
     */
    public function loadCondFormats(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('load', [TableData::class, $table]);
        (new TableRepository())->loadCondFormats($table, auth()->id());

        return $table->_cond_formats;
    }

    /**
     * @param GetTableRequest $request
     * @return \Illuminate\Support\Collection|\Vanguard\Models\DataSetPermissions\CondFormat[]
     * @throws AuthorizationException
     */
    public function loadDDLs(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('load', [TableData::class, $table]);
        (new DDLRepository())->loadForTable($table);

        return $table->_ddls;
    }

    /**
     * @param GetTableRequest $request
     * @return \Illuminate\Support\Collection|\Vanguard\Models\DataSetPermissions\CondFormat[]
     * @throws AuthorizationException
     */
    public function loadRefConds(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('load', [TableData::class, $table]);
        (new TableRefConditionRepository())->loadForTable($table);

        return $table->_ref_conditions;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateKanban(Request $request)
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
     * @param Request $request
     * @return array|mixed
     * @throws AuthorizationException
     */
    public function attachDetachKanbanFld(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
        if ($request->kanban_id && $request->field_id) {
            $repo = new TableKanbanRepository();
            $repo->attachIfNeeded($request->kanban_id, $request->field_id);
            $repo->changePivotFld($request->kanban_id, $request->field_id, $request->setting, $request->val);
            return $repo->getKanban($request->kanban_id);
        }
        return [];
    }

    /**
     * @param ToggleKanbanRightRequest $request
     * @return ResponseFactory|Response|mixed
     */
    public function toggleKanbanRight(ToggleKanbanRightRequest $request)
    {
        $repo = new TableKanbanRepository();
        $kanban = $repo->getKanban($request->kanban_id, false);
        if ($kanban->_field->_table->user_id == auth()->id()) {
            return $repo->toggleKanbanRight($kanban, $request->permission_id, $request->can_edit);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param ToggleKanbanRightRequest $request
     * @return ResponseFactory|Response|mixed
     */
    public function delKanbanRight(ToggleKanbanRightRequest $request)
    {
        $repo = new TableKanbanRepository();
        $kanban = $repo->getKanban($request->kanban_id, false);
        if ($kanban->_field->_table->user_id == auth()->id()) {
            return $repo->deleteKanbanRight($kanban, $request->permission_id);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param Request $request
     * @return ResponseFactory|TableKanbanSettings
     */
    public function copyKanbanSett(Request $request)
    {
        $repo = new TableKanbanRepository();
        $kanban = $repo->getKanban($request->to_kanban_id, false);
        if ($kanban->_field->_table->user_id == auth()->id()) {
            return $repo->copyKanbanSett($request->from_kanban_id, $request->to_kanban_id, !!$request->field_pivot);
        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function kanbanAuth(Request $request): array
    {
        $repo = new TableKanbanRepository();
        $kanban = $repo->getKanban($request->kanban_id, false);
        if ($kanban->_field->_table->user_id != auth()->id()) {
            abort(403, 'Forbidden');
        }
        return [$repo, $kanban];
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function addGroupParam(Request $request)
    {
        /** @var TableKanbanRepository $repo */
        /** @var TableKanbanSettings $kanban */
        [$repo, $kanban] = $this->kanbanAuth($request);
        $repo->addGroupParam($kanban, $request->fields);
        return $kanban->_group_params()->get();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateGroupParam(Request $request)
    {
        /** @var TableKanbanRepository $repo */
        /** @var TableKanbanSettings $kanban */
        [$repo, $kanban] = $this->kanbanAuth($request);
        $param = $kanban->_group_params()->where('id', $request->param_id)->first();
        $repo->updateGroupParam($param, $request->fields);
        return $kanban->_group_params()->get();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function deleteGroupParam(Request $request)
    {
        /** @var TableKanbanRepository $repo */
        /** @var TableKanbanSettings $kanban */
        [$repo, $kanban] = $this->kanbanAuth($request);
        $param = $kanban->_group_params()->where('id', $request->param_id)->first();
        $repo->deleteGroupParam($param, $request->fields);
        return $kanban->_group_params()->get();
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function copyHeaderTo(Request $request)
    {
        $tableTo = $this->tableService->getTable($request->to_table_id);
        $this->authorize('isOwner', [TableData::class, $tableTo]);
        return (new ImportService())->copyFieldTo($request->from_field_id, $tableTo);
    }
}
