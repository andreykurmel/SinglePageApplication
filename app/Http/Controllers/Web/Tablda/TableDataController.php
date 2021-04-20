<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Vanguard\AppsModules\AppOnChangeHandler;
use Vanguard\Classes\SysColumnCreator;
use Vanguard\Classes\TabldaUser;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\TableData\DeleteTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\FavoriteAllRowsRequest;
use Vanguard\Http\Requests\Tablda\TableData\FavoriteToggleRowRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetDDLvaluesRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\LoadHeadersTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\LoadLinkSrcRequest;
use Vanguard\Http\Requests\Tablda\TableData\MapIconRequest;
use Vanguard\Http\Requests\Tablda\TableData\PostTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\PutTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\ReplaceDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\SearchDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\SelectedRowsRequest;
use Vanguard\Http\Requests\Tablda\TableData\ToggleCheckBoxesRequest;
use Vanguard\Jobs\RecalcTableFormula;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\PlanRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\User;

class TableDataController extends Controller
{

    private $service;
    private $tableDataService;
    private $tableService;
    private $permissionsService;
    private $tableViewRepository;
    private $DDLRepository;
    private $fieldRepository;
    private $planRepository;
    private $userService;
    private $refConditionRepository;
    private $tablePermissionService;
    private $fileRepository;

    /**
     * TableDataController constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->tableDataService = new TableDataService();
        $this->tableService = new TableService();
        $this->permissionsService = new UserPermissionsService();
        $this->tableViewRepository = new TableViewRepository();
        $this->DDLRepository = new DDLRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->planRepository = new PlanRepository();
        $this->userService = new UserService();
        $this->refConditionRepository = new TableRefConditionRepository();
        $this->tablePermissionService = new TablePermissionService();
        $this->fileRepository = new FileRepository();
    }

    /**
     * Favorite Toggle Row
     *
     * @param FavoriteToggleRowRequest $request
     * @return array
     */
    public function favoriteToggleRow(FavoriteToggleRowRequest $request)
    {
        return $this->tableDataService->favoriteToggleRow($request->all());
    }

    /**
     * Favorite Toggle All Rows
     *
     * @param FavoriteAllRowsRequest $request
     * @return array
     */
    public function toggleAllFavorites(FavoriteAllRowsRequest $request)
    {
        $user = auth()->check() && $request->user_id ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return ($request->rows_ids
            ? $this->tableDataService->toggleSelectedFavorites($table, $request->rows_ids, $request->status)
            : $this->tableDataService->toggleAllFavorites($table, $request->request_params, $request->status));
    }

    /**
     * Load Table's (or View) Preset.
     *
     * @param LoadHeadersTableDataRequest $request
     * @return string
     */
    public function loadPreset(LoadHeadersTableDataRequest $request)
    {
        return $this->tableService->getQueryPreset($request->table_id, $request->special_params);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function allForLoad(Request $request)
    {
        $user = TabldaUser::get($request->all());

        if ($request->ref_cond_id) {
            $ref_cond = (new TableRefConditionRepository())->getRefCondition($request->ref_cond_id);
            $table_id = $ref_cond ? $ref_cond->ref_table_id : null;
        } else {
            $table_id = $request->table_id;
        }

        $table_t = $this->tableService->getTable($table_id);
        $this->authorizeForUser($user, 'load', [TableData::class, $table_t, HelperService::webHashFromReq($request)]);
        return [$table_id, $user];
    }

    /**
     * Get table info and headers for current user
     *
     * @param LoadHeadersTableDataRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function loadHeaders(LoadHeadersTableDataRequest $request)
    {
        [$table_id, $user] = $this->allForLoad($request);
        if ($request->ref_cond_id) {
            $request->special_params = [];
        }
        $table = $this->tableService->getWithFields($table_id, $user->id, $request->special_params ?: []);
        return $table->toArray();
    }

    /**
     * Get table info and headers for current user
     *
     * @param LoadHeadersTableDataRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function loadJustFields(LoadHeadersTableDataRequest $request)
    {
        [$table_id, $user] = $this->allForLoad($request);
        $table = $this->tableService->getWithFields($table_id, $user->id, $request->special_params ?: [], true);
        return $table->toArray();
    }

    /**
     * @param LoadLinkSrcRequest $request
     * @return TableFieldLink
     */
    public function loadLinkSrc(LoadLinkSrcRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $link = TableFieldLink::where('id', $request->link_id)->first();
        return $this->tableService->getLinkSrc($table, $link);
    }

    /**
     * @param SearchDataRequest $request
     * @return array
     */
    public function search(SearchDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);
        return $this->tableDataService->searchData($table, $request->term, $request->columns);
    }

    /**
     * Find Rows For Replace.
     *
     * @param ReplaceDataRequest $request
     * @return array
     */
    public function findReplace(ReplaceDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);
        [$total, $can] =  $this->tableDataService->findReplace($table, $request->term ?: '', $request->columns, $request->request_params);
        return [
            'total' => $total,
            'can_replace' => $can,
        ];
    }

    /**
     * Replace Data in Rows.
     *
     * @param ReplaceDataRequest $request
     * @return array
     */
    public function doReplace(ReplaceDataRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $table->_fields = $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id(), 'edit');
        $table_fields = $this->getUpdateTableFields($table, $request->columns, false);

        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'update', [TableData::class, $table]);

        $rep = [
            'old_val' => $request->term ?: '',
            'new_val' => $request->replace ?: '',
        ];
        return ['status' => $this->tableDataService->doReplace($table, $rep, $table_fields, $request->request_params)];
    }

    /**
     * Table Fields which are allowed for editing.
     *
     * @param Table $table
     * @param array $request_fields
     * @param bool $with_keys
     * @return array
     */
    private function getUpdateTableFields(Table $table, array $request_fields, bool $with_keys = true)
    {
        if ($table->is_system) {
            return $request_fields;
        }
        //$sysColumn = new SysColumnCreator();

        $edit_fields = ['id', 'row_order', '_defaults_applied', '_applied_cond_formats', '_applied_row_groups'];
        foreach ($table->_fields as $fld) {
            $edit_fields[] = $fld->field;
            if ($fld->input_type === 'Formula') {
                $edit_fields[] = $fld->field . '_formula';
            }
        }
        $table_fields = [];
        foreach ($request_fields as $el => $val) {
            if ($with_keys) {
                if (in_array($el, $edit_fields)) {
                    $table_fields[$el] = $val;
                }
            } else {
                if (in_array($val, $edit_fields)) {
                    $table_fields[] = $val;
                }
            }
        }
        return $table_fields;
    }

    /**
     * @param Table $table
     * @param array $request_fields
     * @return array
     */
    private function specialRules(Table $table, array $request_fields)
    {
        if ($table->db_name === 'table_fields__for_tooltips') {
            $request_fields = collect($request_fields)
                ->only(['id','tooltip','tooltip_show'])
                ->toArray();
        }
        return $request_fields;
    }

    /**
     * Get user`s table rows
     *
     * @param GetTableDataRequest $request
     * @return array: [
     *      'rows' => array,
     *      'rows_count' => int,
     *      'filters' => array
     *   ];
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function get(GetTableDataRequest $request)
    {
        [$table_id, $user] = $this->allForLoad($request);
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        switch ($table->db_name) {
            case 'sum_usages':
                $res = $this->tableService->getSumUsages($request->all(), $user->id);
                break;
            case 'fees':
                $res = $this->tableService->getFees();
                break;
            case 'user_subscriptions':
                $res = $this->tableService->getSubscriptions($request->all(), $user->id);
                break;
            default:
                $res = $this->tableDataService->getRows($request->all(), $user->id);
                break;
        }
        return $res;
    }

    /**
     * Get map bounds
     *
     * @param GetTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMapBounds(GetTableDataRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->tableDataService->getMapThing('Bounds', $table, $request->all(), $user->id);
    }

    /**
     * Get map markers
     *
     * @param GetTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMapMarkers(GetTableDataRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->tableDataService->getMapThing('Markers', $table, $request->all(), $user->id);
    }

    /**
     * Get map markers
     *
     * @param GetTableRequest $request
     * @return Object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMarkerPopup(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->tableDataService->getMarkerPopup($table, $request->row_id);
    }

    /**
     * Insert new row into the user`s table rows
     *
     * @param PostTableDataRequest $request
     * @return mixed
     */
    public function insert(PostTableDataRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'insert', [TableData::class, $table]);

        $is_admin = auth()->user() ? auth()->user()->isAdmin() : false;
        $available_rows = $user->id && (int)$user->_available_features->row_table && !$is_admin
            ? (int)$user->_available_features->row_table
            : PHP_INT_MAX;

        $current_rows = (new TableDataQuery($table))->getQuery()->count() + 1;

        if ($available_rows < $current_rows) {
            return response('The table exceeds the record limit allowed by your permission for "Add New Record"', 403);
        }

        /*if ($request->from_link_id) {
            $linkrepo = new TableFieldLinkRepository();
            $add_link = $linkrepo->getLink($request->from_link_id);
            if (!is_nan($add_link->add_record_limit) && ($add_link->already_added_records >= $add_link->add_record_limit)) {
                return response('Reached limit of adding records via Link Popup!', 403);
            } else {
                $linkrepo->updateLink($add_link, ['already_added_records' => $add_link->already_added_records+1]);
            }
        }*/

        $value_fields = $request->fields;
        $tmp_files = $value_fields['_temp_id'] ?? '';
        $value_fields = $this->getUpdateTableFields($table, $value_fields);

        $row_id = $this->tableDataService->insertRow($table, $value_fields, $user->id);

        if ($row_id) {
            $this->tableDataService->recalcTableFormulas($table, $user->id, [$row_id]);

            if ($tmp_files) {
                $this->fileRepository->saveTempFiles($table, $tmp_files, $row_id);
            }

            $data = array_merge($request->get_query, [
                'table_id' => $table->id,
                'row_id' => $row_id,
                'page' => 1,
                'rows_per_page' => 1
            ]);
            return $this->tableDataService->getRows($data, $user->id);
        } else {
            return response('Corrupted Table incorrect primary key', 500);
        }
    }

    /**
     * Update user`s table row
     *
     * @param PutTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PutTableDataRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $table->_fields = $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id(), 'edit');
        $table_fields = $this->getUpdateTableFields($table, $request->fields);
        $table_fields = $this->specialRules($table, $table_fields);

        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'update', [TableData::class, $table]);

        $data = array_merge($request->get_query, [
            'table_id' => $table->id,
            'row_id' => $request->row_id,
            'page' => 1,
            'rows_per_page' => 1
        ]);

        $update = true;
        if ($table->is_system) {
            switch ($table->db_name) {
                case 'plans_view':
                    $this->planRepository->updateAllFeatures($table_fields);
                    break;
                case 'fees':
                    $this->planRepository->updatePlansAndAddons($table_fields, $request->row_id);
                    $update = false;
                    break;
                case 'user_subscriptions':
                    $this->userService->updatedByAdmin($table_fields, $request->row_id);
                    $update = false;
                    break;
                case 'payments':
                    $table_fields = collect($table_fields)->only('notes')->toArray();
                    break;
            }

            if ($update) {
                $this->tableDataService->updateRow($table, $request->row_id, $table_fields, auth()->id());
            }
        } else {
            $this->tableDataService->updateRow($table, $request->row_id, $table_fields, auth()->id());
            $this->tableDataService->recalcTableFormulas($table, $user->id, [$request->row_id]);
        }

        return $table->db_name == 'user_subscriptions' ?
            $this->tableService->getSubscriptions($data, auth()->id()) :
            ($update ? $this->tableDataService->getRows($data, auth()->id()) : ['rows'=>[$table_fields]]);
    }

    /**
     * @param GetTableRequest $request
     * @return array
     */
    public function checkRowOnBackend(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $updated_row = $request->updated_row ?: [];
        $updated_row = $this->getUpdateTableFields($table, $updated_row);
        $updated_row = $this->tableDataService->setDefaults($table, $updated_row, $user->id, $request->dcr_permission_id);
        $linked_params = $request->linked_params ?: [];

        $updated_row = $this->saveNewInDb($table, $updated_row);

        //autocomplete DDLs
        if ($table->_fields()->hasAutoFillDdl()->count()) {
            $updated_row = $this->tableDataService->checkAutocompleteNewRow($table, $updated_row, $user->id);
        }
        //check formulas
        $evaluator = new FormulaEvaluatorRepository($table, $user->id);
        $updated_row = $evaluator->recalcRowFormulas($updated_row, false);
        //fill link params
        foreach ($linked_params as $key => $param) {
            $updated_row[$key] = $param;
        }

        $checked_row = $this->saveNewInDb($table, $updated_row);
        //apply APP on Change Handler
        $checked_row = (new AppOnChangeHandler($table))->testRow($checked_row);

        return ['row' => $checked_row];
    }

    /**
     * @param Table $table
     * @param array $updated_row
     * @param bool $light
     * @return array
     */
    protected function saveNewInDb(Table $table, array $updated_row, bool $light = false)
    {
        //save in DB and attach special fields
        if ($table->is_system != 1) {
            $db_row = $this->tableDataService->saveInDbNewRow($table, $updated_row);
            if (!$light) {
                $db_row = $this->tableDataService->getDirectRow($table, $db_row['id']);
                $db_row = is_array($db_row)
                    ? $db_row
                    : ($db_row instanceof Model ? $db_row->getAttributes() : (array)$db_row);
            }
            //new row doesn't have 'id'
            $checked_row = array_merge($updated_row, $db_row);
            $checked_row['id'] = $updated_row['id'];
        } else {
            $checked_row = $updated_row;
        }
        return $checked_row;
    }

    /**
     * Delete row from user`s table
     *
     * @param DeleteTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DeleteTableDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'delete', [TableData::class, $table]);
        return $this->tableDataService->deleteRow($table, $request->row_id);
    }

    /**
     * Insert new row into the user`s table rows
     *
     * @param PostTableDataRequest $request
     * @return mixed
     */
    public function requestInsert(PostTableDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $permission = $this->tablePermissionService->getPermission($request->table_permission_id);
        $row_request = (int)$permission->row_request !== $permission->row_request ? -1 : $permission->row_request;

        if ($row_request == 0 || ($permission->pass && $permission->pass != $request->table_permission_pass)) {
            return response('Forbidden', 403);
        } elseif ($row_request > 0) {
            $permission->row_request--;
            $permission->save();
        }

        $table = $this->tableService->getTable($permission['table_id']);
        $this->authorizeForUser($user, 'insert', [TableData::class, $table, HelperService::webHashFromReq($request)]);

        $value_fields = $this->getUpdateTableFields($table, $request->fields);
        $tmp_files = $value_fields['_temp_id'] ?? '';

        $row_id = $this->tableDataService->insertRowFromRequest($table, $value_fields, auth()->id(), $permission->id);
        if ($row_id) {

            if ($tmp_files) {
                $this->fileRepository->saveTempFiles($table, $tmp_files, $row_id);
            }

            return $row_id;
        } else {
            return response('Corrupted Table incorrect primary key', 500);
        }
    }

    /**
     * Insert new row into the user`s table rows
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function requestFinished(GetTableRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'insert', [TableData::class, $table, HelperService::webHashFromReq($request)]);
        $rows_htmls = collect($request->rows_htmls)->whereIn('id', $request->row_ids)->toArray();
        $this->tableDataService->dataRequestStored($table, $request->row_ids);
        $this->tablePermissionService->sendRequestEmails($table, $request->request_id, $rows_htmls);
        return 1;
    }

    /**
     * Update user`s table row
     *
     * @param PutTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function requestUpdate(PutTableDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $permission = $this->tablePermissionService->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission['table_id']);
        $this->authorizeForUser($user, 'update', [TableData::class, $table, HelperService::webHashFromReq($request)]);

        if ($permission->pass && $permission->pass != $request->table_permission_pass) {
            return response('Forbidden', 403);
        }

        $table->_fields = $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id(), 'edit', $permission->id);
        $edit_fields = $table->_fields->pluck('field')->toArray();
        $table_fields = array_filter($request->fields, function ($el) use ($edit_fields) {
            return in_array($el, $edit_fields);
        });

        $value_fields = $this->getUpdateTableFields($table, $request->fields);
        $this->tableDataService->updateRow($table, $request->row_id, $value_fields, auth()->id());
        $this->tableDataService->recalcTableFormulas($table, auth()->id(), [$request->row_id]);
        return $this->tableDataService->getRows([
            'table_id' => $table->id,
            'row_id' => $request->row_id,
            'page' => 1,
            'rows_per_page' => 1
        ], auth()->id());
    }

    /**
     * Delete row from user`s table
     *
     * @param DeleteTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function requestDelete(DeleteTableDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $permission = $this->tablePermissionService->getPermission($request->table_permission_id);
        $table = $this->tableService->getTable($permission['table_id']);
        $this->authorizeForUser($user, 'delete', [TableData::class, $table]);

        if ($permission->pass && $permission->pass != $request->table_permission_pass) {
            return response('Forbidden', 403);
        }

        return $this->tableDataService->deleteRow($table, $request->row_id);
    }

    /**
     * Delete all selected rows from user`s table
     *
     * @param GetTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteAll(GetTableDataRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'delete', [TableData::class, $table]);

        return $this->tableDataService->deleteAllRows($table, $request->all(), auth()->id());
    }

    /**
     * Delete all selected rows from user`s table
     *
     * @param SelectedRowsRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteSelected(SelectedRowsRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'delete', [TableData::class, $table]);

        return ($request->rows_ids
            ? $this->tableDataService->deleteSelectedRows($table, $request->rows_ids)
            : $this->tableDataService->deleteAllRows($table, $request->request_params, auth()->id()));
    }

    /**
     * Delete all selected rows from user`s table
     *
     * @param SelectedRowsRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function massCopy(SelectedRowsRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'insert', [TableData::class, $table]);

        [$added_ids, $corrs] = $this->tableDataService->massCopy(
            $table,
            $request->rows_ids ?: [],
            $request->request_params ?: [],
            $request->replaces ?: [],
            $request->only_columns ?: []
        );

        if (count($added_ids)) {
            $data = [
                'table_id' => $table->id,
                'row_id' => $added_ids,
                'page' => 1,
                'rows_per_page' => count($added_ids)
            ];
            return $this->tableDataService->getRows($data, auth()->id());
        } else {
            return response('Corrupted Table incorrect primary key', 500);
        }
    }

    /**
     * Get distinct values for one field.
     *
     * @param GetTableRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getDistinctiveField(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $field = $this->fieldRepository->getField($request->field_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $results = [];
        if ($field->f_type == 'User')
        {
            $results = $this->tableDataService->getFieldValues($table, $field, $field->field);
            $results['{$user}'] = '{$user}';
            $results['{$group}'] = '{$group}';
        }
        elseif ($field->input_type != 'Input' && $field->ddl_id)
        {
            $ddl = $this->DDLRepository->getDDLwithRefs($field->ddl_id);
            $vals = $this->tableDataService->getDDLvalues($ddl);
            foreach ($vals as $v) {
                $results[ $v['value'] ] = $v['show'] ?? $v['value'];
            }
        }
        return $results;
    }

    /**
     * Get values for selected DDl.
     *
     * @param GetDDLvaluesRequest $request
     * @return array
     */
    public function getDDLvalues(GetDDLvaluesRequest $request)
    {
        $user = auth()->user() ?: new User();
        $ddl = $this->DDLRepository->getDDLwithRefs($request->ddl_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $ddl ? $ddl->_table : null]);
        return $this->tableDataService->getDDLvalues($ddl, $request->row ?: [], strtolower($request->search ?: ''), 200);
    }

    /**
     * Get first row for selected table field.
     *
     * @param Request $request
     * @return array
     */
    public function getFieldRows(Request $request)
    {
        [$table_id, $user] = $this->allForLoad($request);
        $ref = $this->refConditionRepository->getRefCondition($request->link['table_ref_condition_id']);
        $table = $this->tableService->getTable($table_id);

        [$rows_count, $rows] = $this->tableDataService->getFieldRows($table, $request->link, $request->table_row, $request->page ?: 1, $request->maxlimit ?: 0);
        (new TableRepository())->loadCurrentRight($table, $user->id);

        return [
            'rows' => $rows,
            'rows_count' => $rows_count,
            'references' => $ref->_items()->with('_compared_field', '_field')->get(),
            'table_right' => $table,
        ];
    }

    /**
     * Get first row for selected table field.
     *
     * @param Request $request
     * @return array
     */
    public function getAllValuesForField(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->tableDataService->getAllValuesForField($table->id, $request->field_id);
    }

    /**
     * Toggle CheckBoxes for table.
     *
     * @param ToggleCheckBoxesRequest $request
     * @return array
     */
    public function toggleMassCheckBoxes(ToggleCheckBoxesRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $field = $this->fieldRepository->getField($request->field_id);

        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'update', [TableData::class, $table]);

        $arr = ['status' => $this->tableDataService->updateMassCheckBoxes($table, $request->row_ids, $field->field, $request->status)];

        if ($table->is_system) {
            if ($table->db_name == 'plans_view') {
                $this->planRepository->updateAllFeaturesForAllRows($table, $request->row_ids);
            }
        }

        return $arr;
    }

    /**
     * Get map icons for one field.
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMapIcons(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();

        if ($request->field_id) {
            $field = $this->fieldRepository->getField($request->field_id);
            $table = $field->_table;
        } else {
            $table = $this->tableService->getTable($request->table_id);
            $field = null;
        }

        if (auth()->id()) {
            $this->authorizeForUser($user, 'get', [TableData::class, $table]);
            $table->map_icon_field_id = $field ? $field->id : null;
            $table->map_icon_style = $request->map_style;
            $table->save();
        }

        return $table->map_icon_style == 'dist'
            ? $this->tableService->getMapIcons($table, $field)
            : [];
    }

    /**
     * Add Map Icon
     *
     * @param MapIconRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addMapIcon(MapIconRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $field = $this->fieldRepository->getField($request->table_field_id);
        $table = $field->_table;
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $data = $request->only(['table_field_id', 'row_val', 'height', 'width']);

        return $this->tableService->addMapIcon($table, $field, $data, $request->file('file'));
    }

    /**
     * Update Map Icon
     *
     * @param MapIconRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateMapIcon(MapIconRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $field = $this->fieldRepository->getField($request->table_field_id);
        $table = $field->_table;
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $data = $request->only(['table_field_id', 'row_val', 'height', 'width']);

        return $this->tableService->updateMapIcon($data);
    }

    /**
     * Get map icons for one field.
     *
     * @param MapIconRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delMapIcon(MapIconRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $field = $this->fieldRepository->getField($request->table_field_id);
        $table = $field->_table;
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return $this->tableService->delMapIcon($field->id, $request->row_val);
    }

    /**
     * @param GetTableRequest $request
     * @return mixed
     */
    public function formulasTableRecalc(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        $recalc = Import::create([
            'file' => '',
            'status' => 'initialized'
        ]);
        dispatch(new RecalcTableFormula($table, auth()->id(), $recalc->id));

        return $recalc;
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function infoRow(Request $request)
    {
        $row = '';
        if ($request->table_id && $request->table_row) {
            $row = $this->tableService->infoRow($request->table_id, $request->table_row);
        }
        return ['row' => $row];
    }
}
