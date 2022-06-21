<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\AppsModules\AppOnChangeHandler;
use Vanguard\Classes\TabldaUser;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\TableData\BatchUploadingRequest;
use Vanguard\Http\Requests\Tablda\TableData\DeleteTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\FavoriteAllRowsRequest;
use Vanguard\Http\Requests\Tablda\TableData\FavoriteToggleRowRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetDcrRowsRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetDDLvaluesRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\LoadHeadersTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\LoadLinkSrcRequest;
use Vanguard\Http\Requests\Tablda\TableData\MapIconRequest;
use Vanguard\Http\Requests\Tablda\TableData\MassPutTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\PostTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\PutTableDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\RemoveDuplicatesRequest;
use Vanguard\Http\Requests\Tablda\TableData\ReplaceDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\SearchDataRequest;
use Vanguard\Http\Requests\Tablda\TableData\SelectedRowsRequest;
use Vanguard\Http\Requests\Tablda\TableData\ToggleCheckBoxesRequest;
use Vanguard\Jobs\AllRowsDelete;
use Vanguard\Jobs\RecalcTableFormula;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\PlanRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\MapRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\Permissions\TableDataRequestService;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Services\Tablda\UserService;
use Vanguard\User;

class TableDataController extends Controller
{

    private $tableDataService;
    private $tableService;
    private $DDLRepository;
    private $fieldRepository;
    private $planRepository;
    private $userService;
    private $refConditionRepository;
    private $tablePermissionService;
    private $tableDataRequestService;
    private $fileRepository;

    /**
     * TableDataController constructor.
     */
    public function __construct()
    {
        $this->tableDataService = new TableDataService();
        $this->tableService = new TableService();
        $this->DDLRepository = new DDLRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->planRepository = new PlanRepository();
        $this->userService = new UserService();
        $this->refConditionRepository = new TableRefConditionRepository();
        $this->tablePermissionService = new TablePermissionService();
        $this->tableDataRequestService = new TableDataRequestService();
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
            $table_id = $ref_cond && $ref_cond->incoming_allow ? $ref_cond->ref_table_id : null;
        } else {
            $table_id = $request->table_id;
        }

        $table_t = $this->tableService->getTable($table_id);
        if ($table_t) {
            $this->authorizeForUser($user, 'load', [TableData::class, $table_t, $request->all()]);
        }
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
        return $table ? $table->toArray() : null;
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
        return $table ? $table->toArray() : null;
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
    public function searchOnMap(SearchDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);
        return (new MapRepository())->searchDataInMap($table, $request->term, $request->columns, $request->special_params);
    }

    /**
     * @param RemoveDuplicatesRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeDuplicates(RemoveDuplicatesRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);
        return [
            'total' => $this->tableDataService->removeDuplicates($table, $request->parameters),
        ];
    }

    /**
     * @param RemoveDuplicatesRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function batchUploading(BatchUploadingRequest $request)
    {
        $user = auth()->user() ?: new User();
        $table = $this->tableService->getTableByField($request->url_field_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);
        return [
            'job_id' => $this->tableDataService->uploadingJob($table, $request->url_field_id, $request->attach_field_id, $request->row_group_id),
        ];
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
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);
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

        if (!$request->force) {
            $user = auth()->check() ? auth()->user() : new User();
            $this->authorizeForUser($user, 'update', [TableData::class, $table, $request->request_params ?? []]);
        }

        $rep = [
            'old_val' => $request->term ?: '',
            'new_val' => $request->replace ?: '',
            'force' => !!$request->force,
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

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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
        [$table_id, $user] = $this->allForLoad($request);
        $table = $this->tableService->getTable($table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

        return (new MapRepository)->getMapThing('Bounds', $table, $request->all(), $user->id);
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
        [$table_id, $user] = $this->allForLoad($request);
        $table = $this->tableService->getTable($table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

        return (new MapRepository)->getMapThing('Markers', $table, $request->all(), $user->id);
    }

    /**
     * Get map markers
     *
     * @param GetTableRequest $request
     * @return array
     */
    public function getMarkerPopup(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

        return (new MapRepository())->getMarkerPopup($table, $request->row_id);
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
        $this->authorizeForUser($user, 'insert', [TableData::class, $table, $request->all()]);

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
                $this->fileRepository->storeTempFiles($table, $tmp_files, $row_id);
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
     * @param MassPutTableDataRequest $request
     * @return array|\array[][]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function massUpdate(MassPutTableDataRequest $request)
    {
        $results = [];
        foreach ($request->row_datas as $row_id => $fields) {
            $rows = $results['rows'] ?? [];
            $rows_count = $results['rows_count'] ?? 0;

            $results = $this->rowUpdate([
                'table_id' => $request->table_id,
                'row_id' => $row_id,
                'fields' => $fields,
                'get_query' => $request->get_query,
            ]);

            $results['rows'] = array_merge(($results['rows'] ?? []), $rows);
            $results['rows_count'] = ($results['rows_count'] ?? 0) + $rows_count;
        }

        return $results;
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
        return $this->rowUpdate($request->all());
    }

    /**
     * Update user`s table row
     *
     * @param PutTableDataRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function rowUpdate(array $request): array
    {
        $table = $this->tableService->getTable($request['table_id']);

        $table->_fields = $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id(), 'edit');
        $table_fields = $this->getUpdateTableFields($table, $request['fields']);
        $table_fields = $this->specialRules($table, $table_fields);

        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'update', [TableData::class, $table, $request]);

        $data = array_merge($request['get_query'], [
            'table_id' => $table->id,
            'row_id' => $request['row_id'],
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
                    $this->planRepository->updatePlansAndAddons($table_fields, $request['row_id']);
                    $update = false;
                    break;
                case 'user_subscriptions':
                    $this->userService->updatedByAdmin($table_fields, $request['row_id']);
                    $update = false;
                    break;
                case 'payments':
                    $table_fields = collect($table_fields)->only('notes')->toArray();
                    break;
            }

            if ($update) {
                $this->tableDataService->updateRow($table, $request['row_id'], $table_fields, auth()->id());
            }
        } else {
            $this->tableDataService->updateRow($table, $request['row_id'], $table_fields, auth()->id());
            $this->tableDataService->recalcTableFormulas($table, $user->id, [$request['row_id']]);
        }

        return $table->db_name == 'user_subscriptions' ?
            $this->tableService->getSubscriptions($data, auth()->id()) :
            ($update ? $this->tableDataService->getRows($data, auth()->id()) : ['rows'=>[$table_fields]]);
    }

    /**
     * @param GetTableRequest $request
     * @return array
     * @throws \Exception
     */
    public function checkRowOnBackend(GetTableRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->table_id);

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

        $updated_row = $request->updated_row ?: [];

        if (!empty($updated_row['id'])) {
            throw new \Exception('Autocomplete failed (present row id)', 1);
        }

        $table->__data_dcr_id = $request->dcr_permission_id;
        $updated_row = $this->getUpdateTableFields($table, $updated_row);
        $updated_row = $this->tableDataService->setDefaults($table, $updated_row, $user->id);
        $linked_params = $request->linked_params ?: [];
        $linked_params = $this->tableDataService->changeLinkedForSysTable($table, $linked_params);

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

        //Fix showing of correct 'Data,Field' for new row
        if ($table->db_name == 'correspondence_fields') {
            $collection = collect([ new CorrespField($checked_row) ]);
            $collection = (new TableDataRowsRepository())->attachSpecialFields($collection, $table, auth()->id());
            $checked_row = $collection->first();
        }

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
            if ($db_row && !empty($db_row['id'])) {
                $db_row = $this->tableDataService->getDirectRow($table, $db_row['id'], ['users', 'groups', 'refs', 'conds']);
            }
            //new row doesn't have 'id'
            $db_row = is_array($db_row) ? $db_row : $db_row->toArray();
            $checked_row = array_merge($updated_row, $db_row);
            $checked_row['id'] = $updated_row['id'] ?? null;
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
        $this->authorizeForUser($user, 'delete', [TableData::class, $table, $request->all()]);
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
        $dcr = $this->tableDataRequestService->getDataRequest($request->table_dcr_id);
        $row_request = (int)$dcr->row_request !== $dcr->row_request ? -1 : $dcr->row_request;

        if ($row_request == 0 || ($dcr->pass && $dcr->pass != $request->table_dcr_pass)) {
            return response('Forbidden', 403);
        } elseif ($row_request > 0) {
            $dcr->row_request--;
            $dcr->save();
        }

        $table = $this->tableService->getTable($dcr['table_id']);
        $this->authorizeForUser($user, 'insert', [TableData::class, $table, $request->all()]);

        $value_fields = $this->getUpdateTableFields($table, $request->fields);
        $value_fields['request_id'] = $dcr->id;
        $tmp_files = $request->fields['_temp_id'] ?? '';

        $table->__data_dcr_id = $dcr->id;
        $row_id = $this->tableDataService->insertRow($table, $value_fields, $user->id);
        if ($row_id) {
            $dcr_row = $this->tableDataService->getDirectRow($table, $row_id)->toArray();

            if ($tmp_files) {
                $this->fileRepository->storeTempFiles($table, $tmp_files, $row_id);
            }

            if ($request->dcr_linked_rows) {
                $dcr_row = $this->requestStoreLinkedRows($table, $dcr, $request->dcr_linked_rows, $dcr_row);
            }

            if ($request->html_row) {
                $this->tableDataRequestService->sendRequestEmails($table, $request->table_dcr_id, $request->html_row);
            }

            return $dcr_row;
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
    public function requestUpdate(PutTableDataRequest $request)
    {
        $user = auth()->user() ?: new User();
        $dcr = $this->tableDataRequestService->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($dcr['table_id']);
        $this->authorizeForUser($user, 'update', [TableData::class, $table, $request->all()]);

        if ($dcr->pass && $dcr->pass != $request->table_dcr_pass) {
            return response('Forbidden', 403);
        }

        $table->__data_dcr_id = $dcr->id;
        $table->_fields = $this->fieldRepository->loadFieldsWithPermissions($table, auth()->id(), 'edit');
        $edit_fields = $table->_fields->pluck('field')->toArray();
        $table_fields = array_filter($request->fields, function ($el) use ($edit_fields) {
            return in_array($el, $edit_fields);
        });

        $value_fields = $this->getUpdateTableFields($table, $request->fields);
        $visitor_permis = $this->tablePermissionService->getSysPermission($table->id, 1);
        $ex = [
            'dcr_id' => $dcr->id,
            'visitor_id' => $visitor_permis->id,
        ];
        $this->tableDataService->updateRow($table, $request->row_id, $value_fields, auth()->id(), $ex);
        $this->tableDataService->recalcTableFormulas($table, auth()->id(), [$request->row_id]);

        if ($request->dcr_linked_rows) {
            $dcr_row = $this->tableDataService->getDirectRow($table, $request->row_id, ['none'])->toArray();
            $this->requestStoreLinkedRows($table, $dcr, $request->dcr_linked_rows, $dcr_row);
        }

        if ($request->html_row) {
            $this->tableDataRequestService->sendRequestEmails($table, $request->table_dcr_id, $request->html_row);
        }

        return $this->tableDataService->getRows([
            'table_id' => $table->id,
            'row_id' => $request->row_id,
            'page' => 1,
            'rows_per_page' => 1
        ], auth()->id());
    }

    /**
     * @param Table $table
     * @param TableDataRequest $dcr
     * @param array $dcr_linked_rows
     * @param array $dcr_parent_row
     * @return array
     * @throws \Exception
     */
    protected function requestStoreLinkedRows(Table $table, TableDataRequest $dcr, array $dcr_linked_rows, array $dcr_parent_row)
    {
        $this->tableDataRequestService->storeLinkedRows($dcr, $dcr_linked_rows, $dcr_parent_row);
        $evaluator = new FormulaEvaluatorRepository($table, $table->user_id);
        return $evaluator->recalcRowFormulas($dcr_parent_row);
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
        $dcr = $this->tableDataRequestService->getDataRequest($request->table_dcr_id);
        $table = $this->tableService->getTable($dcr['table_id']);
        $this->authorizeForUser($user, 'delete', [TableData::class, $table, $request->all()]);

        if ($dcr->pass && $dcr->pass != $request->table_dcr_pass) {
            return response('Forbidden', 403);
        }

        return $this->tableDataService->deleteRow($table, $request->row_id, $dcr->id);
    }

    /**
     * @param GetDcrRowsRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getDcrRows(GetDcrRowsRequest $request)
    {
        $linked_dcr = $this->tableDataRequestService->getLinkedTable($request->special_params['dcr_linked_id'] ?? 0);
        $table = $this->tableService->getTable($linked_dcr->linked_table_id);
        $this->authorizeForUser(new User(), 'load', [TableData::class, $table, $request->all()]);

        return ['rows' => $this->tableDataService->getDcrRows($table, $linked_dcr, $request->parent_row_id)];
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

        $this->authorizeForUser($user, 'delete', [TableData::class, $table, $request->all()]);

        if ($request->rows_ids) {
            return $this->tableDataService->deleteSelectedRows($table, $request->rows_ids);
        } else {
            dispatch(new AllRowsDelete($table, $request->request_params, auth()->id()));
            return ['version_hash' => $table->version_hash];
        }
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

        $this->authorizeForUser($user, 'insert', [TableData::class, $table, $request->all()]);

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

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

        $results = [];
        if ($field->f_type == 'User')
        {
            $results = $this->tableDataService->distinctiveFieldValues($table, $field);
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
        if ($ddl->table_id != $request->table_id) {
            return [];
        }
        $this->authorizeForUser($user, 'get', [TableData::class, $ddl->_table, $request->all()]);
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

        if ($ref && $ref->incoming_allow) {
            [$rows_count, $rows] = $this->tableDataService->getFieldRows($table, $request->link, $request->table_row, $request->page ?: 1, $request->maxlimit ?: 0);
            (new TableRepository())->loadCurrentRight($table, $user->id);
            $references = $ref->_items()->with('_compared_field', '_field')->get();
        } else {
            $rows = [];
            $rows_count = 0;
            $references = [];
        }

        return [
            'rows' => $rows,
            'rows_count' => $rows_count,
            'references' => $references,
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
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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
        $this->authorizeForUser($user, 'update', [TableData::class, $table, $request->all()]);

        $arr = ['status' => $this->tableDataService->updateMassCheckBoxes($table, $request->row_ids, $field->field, $request->status)];

        if ($table->is_system) {
            if ($table->db_name == 'plans_view') {
                $this->planRepository->updateAllFeaturesForAllRows($table);
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
            $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);
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
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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
        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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

        $this->authorizeForUser($user, 'get', [TableData::class, $table, $request->all()]);

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
