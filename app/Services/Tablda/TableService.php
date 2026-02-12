<?php

namespace Vanguard\Services\Tablda;


use Exception;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Vanguard\Country;
use Vanguard\Jobs\WatchMirrorValues;
use Vanguard\Models\AppSetting;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Correspondences\CorrespStim3D;
use Vanguard\Models\CountryData;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\FormulaHelper;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Models\UnitConversion;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\Plan;
use Vanguard\Models\User\UserCloud;
use Vanguard\Modules\Permissions\PermissionObject;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\Permissions\CondFormatsRepository;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableGroupingRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableSimplemapRepository;
use Vanguard\Repositories\Tablda\TableTournamentRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Repositories\Tablda\UploadingFileFormatsRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\Permissions\UserPermissionsService;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\User;

class TableService
{
    protected $fileRepository;
    protected $tableRepository;
    protected $tableFieldService;
    protected $folderRepository;
    protected $importRepository;
    protected $tableViewRepository;
    protected $permissionsService;
    protected $userGroupRepository;
    protected $refConditionRepository;
    protected $tableDataRepository;
    protected $service;

    /**
     * TableService constructor.
     */
    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->tableRepository = new TableRepository();
        $this->tableFieldService = new TableFieldService();
        $this->folderRepository = new FolderRepository();
        $this->importRepository = new ImportRepository();
        $this->tableViewRepository = new TableViewRepository();
        $this->permissionsService = new UserPermissionsService();
        $this->userGroupRepository = new UserGroupRepository();
        $this->refConditionRepository = new TableRefConditionRepository();
        $this->tableDataRepository = new TableDataRepository();
        $this->service = new HelperService();
    }

    /**
     * Save Table Status for User.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $data
     * @return mixed
     */
    public function saveStatuse(int $table_id, int $user_id, string $data)
    {
        return $this->tableRepository->saveStatuse($table_id, $user_id, $data);
    }

    /**
     * Update table data.
     *
     * @param $table_id
     * @param array $data
     * @param $user_id
     * @return mixed
     */
    public function updateTable($table_id, array $data, $user_id)
    {
        if (
            !empty($data['name'])
            &&
            isset($data['_folder_id'])
            &&
            $this->tableRepository->testNameOnLvl($data['name'], $data['_folder_id'], $user_id)
        ) {
            return ['error' => true, 'msg' => 'Node Name Taken. Enter a Different Name.'];
        }

        $old_tb = $this->getTable($table_id);
        if (!empty($data['max_mirrors_in_one_row']) && $data['max_mirrors_in_one_row'] != $old_tb->max_mirrors_in_one_row) {
            dispatch(new WatchMirrorValues($old_tb->id));
        }

        if (!empty($data['name']) && $data['name'] != $old_tb->name) {
            (new FolderService())->syncPrivateWithPublicShared();
        }

        if (!empty($data['rows_per_page']) && $data['rows_per_page'] != $old_tb->rows_per_page) {
            $old_tb->activateVirtualScrollIfNeeded();
        }

        if ($data['_changed_prop_name'] ?? '') {
            (new UserRepository())->newMenutreeHash($user_id);
        }

        $this->tableRepository->updateTableSettings($table_id, $data);

        $table = $this->getTable($table_id);
        $this->preloadUnitConvers($table);
        return $table;
    }

    /**
     * Get Table IDs.
     *
     * @param $folder_ids
     * @return array
     */
    public function getIdsFromFolders($folder_ids)
    {
        return $this->tableRepository->getIdsFromFolders($folder_ids);
    }

    /**
     * Test that user can access to table with provided folders path
     *
     * @param $path
     * @return array
     */
    public function objectExists($path)
    {
        $auth = app(AuthUserSingleton::class);
        $folder_tree = $auth->getMenuTree();

        $needed_type = preg_match('/\/$/i', $path) ? ['folder'] : ['table', 'link', 'page'];
        $folders_arr = array_filter(explode('/', $path));
        $object_name = $this->lowerData(array_pop($folders_arr));

        if ($this->service->cur_subdomain == $this->service->public_subdomain) {
            //get table or folder from 'public' tab
            $object = $this->findInTree($object_name, $folders_arr, $folder_tree['public'] ?? [], $needed_type);
            $object = $object['id'] ? $object : $this->findHiddenPublicTable($object_name);
        } else {
            //get table or folder from 'private', 'favorite' (owner) or 'myAccount' tabs
            if (!empty($folder_tree['private'])) {
                $object = $this->findInTree($object_name, $folders_arr, $folder_tree['private'] ?? [], $needed_type);
                $object = $object['id'] ? $object : $this->findInTree($object_name, $folders_arr, $folder_tree['favorite'] ?? [], $needed_type);
                $object = $object['id'] ? $object : $this->findInTree($object_name, $folders_arr, $folder_tree['account'] ?? [], $needed_type);
            } else {
                $object = [
                    'type' => '',
                    'id' => 0
                ];
            }
        }

        if ($object['type'] == 'link') {
            $object['type'] = 'table';
        }
        return $object;
    }

    /**
     * @param string $input
     * @return string
     */
    protected function lowerData(string $input): string
    {
        return urldecode(strtolower($input));
    }

    /**
     * Test that user can access to table with provided folders path
     *
     * @param string $object_name
     * @param array $folders_arr
     * @param $sub_tree
     * @param array $type
     * @return array
     */
    public function findInTree(string $object_name, array $folders_arr, $sub_tree, array $type = [])
    {
        $object = [
            'type' => '',
            'id' => 0
        ];
        if (!count($folders_arr)) {
            foreach ($sub_tree as $item) {
                $accept_type = !$type || in_array($item['li_attr']['data-type'], $type);
                if ($this->lowerData($item['init_name']) == $object_name && $accept_type) {
                    $object = [
                        'type' => $item['li_attr']['data-type'],
                        'id' => $item['li_attr']['data-id']
                    ];
                }
            }
        } else {
            $folder = $this->lowerData(array_shift($folders_arr));
            foreach ($sub_tree as $item) {
                if ($this->lowerData($item['init_name']) == $folder && $item['li_attr']['data-type'] == 'folder') {
                    $object = $this->findInTree($object_name, $folders_arr, $item['children'], $type);
                }
            }
        }
        return $object;
    }

    /**
     * Find table by name through hidden public tables.
     *
     * @param string $object_name
     * @return array
     */
    public function findHiddenPublicTable(string $object_name)
    {
        $tb = Table::where('name', $object_name)
            ->where('pub_hidden', 1)
            ->whereHas('_public_links')
            ->first();

        return [
            'type' => $tb ? 'table' : '',
            'id' => $tb ? $tb->id : 0
        ];
    }

    /**
     * Test that exists table request.
     *
     * @param $code (stings with 'hash' or 'table_id/.../user_link' or 'custom url'
     * @return TableDataRequest
     */
    public function tableRequest($code)
    {
        $dcr = TableDataRequest::where('custom_url', '=', $code)
            ->where('active', 1)
            ->whereHas('_user', function ($q) {
                $q->where('subdomain', '=', $this->service->cur_subdomain);
            })
            ->first();
        if (!$dcr) {
            $dcr = TableDataRequest::where('link_hash', '=', $code)
                ->where('active', 1)
                ->first();
        }

        if ($dcr) {
            $dcr->load(['_dcr_linked_tables', '_fields_pivot', '_default_fields', '_dcr_rights', '_data_request_columns']);
            return $dcr;
        } else {
            return null;
        }
    }

    /**
     * Test that view exists.
     *
     * @param $hash
     * @return TableView
     */
    public function viewExists($hash)
    {
        $view = $this->tableViewRepository->getByCustomUrl($hash);

        if (!$view) {
            $path = explode('/', $hash);
            if (count($path) > 1) {
                $view = $this->tableViewRepository->getByTbIdNameAndAddress($path);
            } else {
                $view = $this->tableViewRepository->getByHash($hash);
            }
        }

        if ($view) {
            $view->_for_user_or_active = $view->is_active
                || $view->user_id == auth()->id()
                || $view->_table_permissions()->applyIsActiveForUserOrPermission(auth()->id())->count();
        }
        return $view;
    }

    /**
     * Get Table or View preset.
     *
     * @param $table_id
     * @param array $special_params
     * @return mixed|object
     */
    public function getQueryPreset($table_id, array $special_params = [])
    {
        //loaded from Folder View
        if (!empty($special_params['is_folder_view'])) {
            $special_params['view_hash'] = $this->folderRepository->getAssignedView($special_params['is_folder_view'], $table_id);
        }
        $view_hash = $special_params['view_hash'] ?? '';

        if ($view_hash) {
            $view_object = $this->tableViewRepository->getByHash($view_hash);
            $view_data = $view_object ? json_decode($view_object->data, 1) : [];
            $data = [];
            if ($view_object && $view_object->can_sorting) {
                $data['sort'] = $view_data['sort'] ?? [];
            }
            if ($view_object && $view_object->can_filter) {
                $data['applied_filters'] = $view_data['applied_filters'] ?? [];
            }
            if ($view_object && $view_object->can_hide) {
                $data['hidden_row_groups'] = $view_data['hidden_row_groups'] ?? [];
            }
        } else {
            $table = $this->tableRepository->getTable($table_id);
            if ($table->_owner_settings && $table->_owner_settings->initial_view_id > 0) {
                $view_object = $table->_owner_settings->_initial_view()->first();
                $data = $view_object->data ?? null;
            } elseif ($table->_owner_settings && $table->_owner_settings->initial_view_id == -1) {
                $data = $table->_cur_statuse ? $table->_cur_statuse->status_data : null;
            } else {
                $data = null;
            }
        }
        return $data ?: '{}';
    }

    /**
     * @param string $view_hash
     * @param Table|null $table
     * @return array
     */
    public function findViewCols(string $view_hash, Table $table = null): array
    {
        $view_object = $this->tableViewRepository->getByHash($view_hash);
        if ($table) {
            $table->__data_permission_id = $view_object->access_permission_id ?? -1;//View Permission OR View without Permissions
        }
        $v_data = $view_object ? json_decode($view_object->data, 1) : [];
        $view_cols['temp_hidden'] = $view_object && $view_object->can_hide ? ($v_data['hidden_columns'] ?? []) : [];//view temp hidden columns
        $view_cols['orders'] = $view_object && $view_object->column_order ? ($v_data['order_columns'] ?? []) : [];//view order columns
        $view_cols['visible'] = [];//view order columns // $v_data['visible'] ?? []
        if ($view_object && $view_object->_col_group) {
            $from_col = $view_object->_col_group->_fields()
                ->select('field')
                ->get()
                ->pluck('field');
            $view_cols['visible'] = array_merge($view_cols['visible'], $from_col->toArray());
        }
        return $view_cols;
    }

    /**
     * Get table info with rights, fields and user settings for each field
     *
     * @param $table_id
     * @param $user_id
     * @param array $special_params
     * @param bool $light
     * @return Table
     */
    public function getWithFields($table_id, $user_id, array $special_params = [], bool $light = false)
    {
        if (!$table_id) {
            return null;
        }
        $user_id = $this->service->forceGuestForPublic($user_id);

        //loaded from Folder View
        if (!empty($special_params['is_folder_view'])) {
            $special_params['view_hash'] = $this->folderRepository->getAssignedView($special_params['is_folder_view'], $table_id);
        }

        $view_hash = $special_params['view_hash'] ?? '';
        $view_cols = [];

        $table = $this->tableRepository->getTableByIdOrDB($table_id);
        $table->__data_permission_id = null;
        $table->__data_dcr_id = null;

        if ($view_hash) {
            $view_cols = $this->findViewCols($view_hash, $table);
        } else {
            $table->__data_dcr_id = $special_params['table_dcr_id'] ?? null;//DCR right
        }

        //User is owner if it is Table OR View loaded in edit mode (not owner if it is DataRequest OR View loaded from public link)
        $permis = $this->tableRepository->loadCurrentRight($table, $user_id);

        $table->_is_favorite = $this->tableRepository->isFavorite($table_id, $user_id);
        $table->google_api_key = HelperService::getTableGoogleApi($table);

        //load
        if (!$light) {

            $this->tableRepository->loadCondFormats($table, $permis->is_owner);
            $this->tableRepository->loadOwnerSettings($table);
            $table->load([
                '_fields_pivot', //SRV
                '_saved_filters:id,table_id,user_id,name,related_colgroup_id',
                '_user' => function ($q) {
                    $q->with('_subscription', '_available_features:id,q_tables,row_table');
                    $q->select('id', 'email', 'username', 'plan_feature_id');
                },
                '_theme',
                '_cur_settings',
                '_views' => function ($v) use ($table, $permis) {
                    $v->with('_view_rights', '_filtering');
                    $v->where('user_id', $permis->is_owner);
                    $vPermisId = $this->service->viewPermissionId($table);
                    if (!$permis->is_owner && $vPermisId != -1) {
                        //get only 'shared' tableViews for regular User.
                        $v->orWhereHas('_table_permissions', function ($tp) use ($vPermisId) {
                            $tp->applyIsActiveForUserOrPermission($vPermisId);
                        });
                    }
                },
                '_user_notes',
                '_row_groups' => function ($q) use ($permis, $table) {
                    $q->orderBy('is_system', 'desc');
                    $q->with('_regulars');
                    if (!$permis->is_owner) {
                        //get only 'shared' rowGroups for regular User.
                        $q->whereIn('id', $permis->shared_row_groups);
                    }
                },
                '_column_groups' => function ($q) use ($permis, $table) {
                    $q->orderBy('is_system', 'desc');
                    $q->with('_fields');
                    if (!$permis->is_owner) {
                        //get only 'shared' columnGroups for regular User.
                        $q->whereIn('id', $permis->shared_col_groups);
                    }
                },
                '_attached_files',
                '_link_initial_folder' => function ($lif) {
                    $lif->with([
                        '_folder' => function ($f) {
                            $f->with('_root_folders');
                        }
                    ]);
                }
            ]);
            $table->_gen_row_groups = $table->_row_groups()->select('id', 'name')->get();
            $table->_gen_col_groups = $table->_column_groups()
                ->with('_fields:table_fields.id,table_fields.name,table_fields.field')
                ->select('id', 'name')
                ->get();
            (new DDLRepository())->loadForTable($table);
            (new TableRefConditionRepository())->loadForTable($table);

            //set Settings from Owner if they are not available
            if (!$table->_is_owner && $table->_current_right && !$table->_current_right->can_edit_tb) {
                $table->setRelation('_cur_settings', $table->_owner_settings);
            }

            //Set Settings To CondFormats for different Users
            $condRepo = new CondFormatsRepository();
            foreach ($table->_cond_formats as $format) {
                $condRepo->prepareCondFormatFields($format, $user_id);
            }
            $condRepo->fixRowOrder($table->_cond_formats);

            //LOAD ADDITIONAL SETTINGS
            $this->loadMoreTableSettings($table, $permis, $user_id);
        }


        $table_fields = $this->tableFieldService->getWithSettings($table, $user_id, $view_cols);
        $this->tableFieldService->loadFldRelations($table_fields, $table);

        //create url for header fields which have links to other tables
        foreach ($table_fields as $t_field) {
            foreach ($t_field->_links as $t_link) {
                $t_link = $this->getLinkSrc($table, $t_link);
            }
        }
        //no '_permis_hidden'
        if ($special_params['no_permission_hidden'] ?? []) {
            foreach ($table_fields as $t_field) {
                $t_field['_permis_hidden'] = ! in_array($t_field['id'], $special_params['no_permission_hidden']);
            }
        }
        $table->setRelation('_fields', $table_fields);

        $table->icons_array =
            ($table->_link_initial_folder && $table->_link_initial_folder->_folder) ?
                $table->_link_initial_folder->_folder->_root_folders :
                [];

        //not needed
        unset($table->_link_initial_folder);

        //get total rows count
        try {
            $table->_rows_count = (new TableDataQuery($table))->getQuery()->count();
        } catch (Exception $e) {
        }

        return $table;
    }

    /**
     * @param Table $table
     * @param PermissionObject $permis
     * @param $user_id
     * @return void
     * @throws Exception
     */
    private function loadMoreTableSettings(Table $table, PermissionObject $permis, $user_id)
    {
        $vPermisId = $this->service->viewPermissionId($table);
        //Load Charts for Table 'add_bi'
        (new ChartService())->emptyChartsToTab($table);
        //saved Charts for BI
        $table->load([
            '_bi_charts' => function ($q) use ($table, $user_id, $permis, $vPermisId) {
                $q->where(function ($v) use ($table, $user_id, $permis, $vPermisId) {
                    $v->where('user_id', '=', $permis->is_owner);
                    if (!$permis->is_owner && $vPermisId != -1) {
                        //get only 'shared' tableCharts for regular User.
                        $v->orWhereHas('_table_permissions', function ($tp) use ($table, $vPermisId) {
                            $tp->applyIsActiveForUserOrPermission($vPermisId);
                        });
                    }
                });
                $q->with([
                    '_table_permissions' => function ($tp) use ($table, $vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                        $tp->with([
                            '_addons' => function ($adn) {
                                $adn->isBi();
                            }
                        ]);
                    },
                    '_chart_rights',
                ]);
            },
            '_chart_tabs',
        ]);

        //Global settings for BI
        $adns = $table->_bi_charts->pluck('_table_permissions')
            ->flatten()->pluck('_addons')
            ->flatten()->pluck('_link');
        $table->_collaborator_bi_settings = [
            '_is_owner' => !!$permis->is_owner,
            'avail_fix_layout' => $adns->where('lockout_layout', '=', 1)->count(),
            'avail_can_add' => $adns->where('add_new', '=', 1)->count(),
            'avail_hide_settings' => $adns->where('hide_settings', '=', 1)->count(),
            'avail_cell_spacing' => $adns->where('block_spacing', '=', 1)->count(),
            'avail_cell_height' => $adns->where('vert_grid_step', '=', 1)->count(),
            'avail_corner_radius' => $adns->where('crnr_radius', '=', 1)->count(),
        ];

        //decode jsons
        foreach ($table->_bi_charts as $i => $chart) {
            $chart_ar = $chart->toArray();
            unset($chart_ar['_table_permissions']);
            if (!$table->_is_owner) {
                unset($chart_ar['_chart_right']);
            }

            $chart_ar['active'] = true;
            $chart_ar['chart_settings'] = json_decode($chart->chart_settings);
            $chart_ar['cached_data'] = json_decode($chart->cached_data);
            $chart_ar['_can_edit'] = ($permis->is_owner) || ($chart->_table_permissions->where('_right.can_edit', '=', 1)->count());
            $chart_ar['__gs_hash'] = Uuid::uuid4();
            $table->_bi_charts[$i] = $chart_ar;
        }
        //^^^

        //Load Alerts 'add_alert'
        $table->load([
            '_alerts' => function ($q) use ($table, $user_id) {

                if ($user_id != $table->user_id) {
                    $q->isAvailForUser($user_id);

                    //'can_edit' permission
                    $q->with([
                        '_table_permissions' => function ($tp) {
                            $tp->isActiveForUserOrVisitor();
                        }
                    ]);
                }

                $q->with(TableAlertsRepository::relations());
            },
        ]);
        foreach ($table->_alerts as $alert) {
            $alert->_can_edit = ($user_id == $table->user_id && !$vPermisId)
                || ($alert->_table_permissions->where('_right.can_edit', '=', 1)->count());
            unset($alert->_table_permissions);
        }
        //^^^

        //Load Kanban settings for table with relative Addon 'add_kanban'
        $table->load([
            '_kanban_settings' => function ($q) use ($table, $permis, $vPermisId) {
                if (!$permis->is_owner && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $q->whereHas('_table_permissions', function ($tp) use ($table, $vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $q->with('_fields_pivot', '_kanban_rights', '_group_params');
            },
        ]);

        $_table_permissions = $this->getTbPermissions($table);
        $table->setRelation('_table_permissions', $_table_permissions);

        //set Permissions2UserGroups datas
        $table->_user_groups_2_table_permissions = $_table_permissions
            ->flatten()
            ->pluck('_user_groups')
            ->flatten()
            ->pluck('pivot');

        // GET ADDITIONAL TABLE SETTINGS FOR OWNER
        if ($table->_is_owner) {
            $table->load([
                '_table_references' => function ($q) {
                    $q->with(['_reference_corrs']);
                },
                '_communications' => function ($q) {
                    $q->with('_from_user', '_to_user', '_to_user_group');
                },
                '_backups',
            ]);

        } else {

            $auth = app(AuthUserSingleton::class);
            $table->load(['_communications' => function ($q) use ($user_id, $auth) {
                $q->with('_from_user', '_to_user', '_to_user_group');
                $q->where('from_user_id', '=', $user_id);
                $q->orWhere('to_user_id', '=', $user_id);
                $q->orWhereIn('to_user_group_id', $auth->getUserGroupsMember()->pluck('id'));
                $q->orWhere(function ($or) {
                    $or->where('to_user_id', '=', 0);
                    $or->whereNull('to_user_group_id');
                });
            }]);

            //get owner theme
            $owner = User::where('id', $table->user_id)->first();
            $table->_owner_theme = AppTheme::where('id', ($owner ? $owner->app_theme_id : null))->first();

        }

        $this->preloadUnitConvers($table);

        (new TableSimplemapRepository())->loadForTable($table, $user_id);//Load relations for 'add_simplemap'
        (new TableDataRequestRepository())->loadForTable($table, $user_id);//Load relations for 'add_request'
        (new TableTournamentRepository())->loadForTable($table, $user_id);//Load relations for 'add_tournament'
        (new TableGroupingRepository())->loadForTable($table, $user_id);//Load relations for 'add_grouping'
        (new TableEmailAddonService())->loadForTable($table, $user_id);//Load relations for 'add_email'
        (new TableTwilioAddonService())->loadForTable($table, $user_id);//Load relations for 'add_twilio'
        (new TableGanttService())->loadForTable($table, $user_id);//Load relations for 'add_gantt'
        (new TableMapService())->loadForTable($table, $user_id);//Load relations for 'add_map'
        (new TableCalendarService())->loadForTable($table, $user_id);//Load relations for 'add_calendar'
        (new TableReportService())->loadForTable($table, $user_id);//Load relations for 'add_report'
        (new TableAiService())->loadForTable($table, $user_id);//Load relations for 'add_ai'
    }

    /**
     * @param Table|null $table
     * @return void
     */
    protected function preloadUnitConvers(Table $table = null)
    {
        if (!$table) {
            return;
        }

        //UNIT CONVERSIONS
        $convers = [];
        if ($table->unit_conv_is_active
            && $table->unit_conv_by_user
            && $table->_uc_table
            && $table->unit_conv_from_fld_id
            && $table->unit_conv_to_fld_id
            && $table->unit_conv_operator_fld_id
            && $table->unit_conv_factor_fld_id
        ) {
            $uc_fields = $this->tableFieldService->selFields($table->unit_conv_table_id, [
                $table->unit_conv_from_fld_id, $table->unit_conv_to_fld_id, $table->unit_conv_operator_fld_id,
                $table->unit_conv_factor_fld_id, $table->unit_conv_formula_fld_id, $table->unit_conv_formula_reverse_fld_id
            ]);

            $db_from = $uc_fields->where('id', '=', $table->unit_conv_from_fld_id)->first();
            $db_to = $uc_fields->where('id', '=', $table->unit_conv_to_fld_id)->first();
            $db_operator = $uc_fields->where('id', '=', $table->unit_conv_operator_fld_id)->first();
            $db_factor = $uc_fields->where('id', '=', $table->unit_conv_factor_fld_id)->first();
            $db_formula = $uc_fields->where('id', '=', $table->unit_conv_formula_fld_id)->first();
            $db_formula_rev = $uc_fields->where('id', '=', $table->unit_conv_formula_reverse_fld_id)->first();

            if ($db_from && $db_to && $db_operator && $db_factor) {
                $rows = (new TableDataQuery($table->_uc_table))->getQuery()->get();
                foreach ($rows as $row) {
                    $convers[] = [
                        'from_unit' => $row->{$db_from->field},
                        'to_unit' => $row->{$db_to->field},
                        'operator' => $row->{$db_operator->field},
                        'factor' => $row->{$db_factor->field},
                        'formula' => $db_formula ? $row->{$db_formula->field} : '',
                        'formula_reverse' => $db_formula_rev ? $row->{$db_formula_rev->field} : '',
                    ];
                }
            }
        }
        $table->__unit_convers = $convers;
    }

    /**
     * @param Table $table
     * @param int|null $individual
     * @return Collection
     */
    public function getTbPermissions(Table $table, int $individual = null)
    {
        $_table_permissions = (new TablePermissionRepository())->loadPermisWithRelations($table->id);

        if ($individual) {
            $_table_permissions->where('id', '=', $individual);
        }

        if (!$table->_is_owner) {
            $_table_permissions = $_table_permissions->where(function ($q) {
                $q->isActiveForUserOrVisitor();
            })
                ->get();
            foreach ($_table_permissions as $idx => $permis) {
                if (!$permis->is_system && !$permis->is_request) {
                    $permis->name = 'SHARED' . ($idx > 0 ? $idx + 1 : '');
                }
            }
        } else {
            $_table_permissions = $_table_permissions->get();
        }

        return $_table_permissions;
    }

    /**
     * Get SRC params for creating table links.
     *
     * @param Table $table
     * @param TableFieldLink $t_link
     * @return TableFieldLink
     */
    public function getLinkSrc(Table $table, TableFieldLink $t_link)
    {
        $t_link->_src_table_link = '';
        $t_link->_src_conditions = [];
        if (in_array($t_link->link_display, ['Table', 'RorT']) && $t_link->table_ref_condition_id) {

            $ref_cond = $table->_ref_conditions->where('id', $t_link->table_ref_condition_id)->first();
            if ($ref_cond && $ref_cond->_ref_table) {
                $t_link->_src_table_link = $this->getTablePath($ref_cond->_ref_table);
                $_src_conditions = [];
                foreach ($ref_cond->_items as $idx => $cond) {
                    if ($cond->_compared_field && $cond->compared_operator == '=') {
                        $_src_conditions[] = [
                            'field' => ($cond->item_type == 'P2S' && $cond->_field ? $cond->_field->field : null),
                            'compared' => $cond->_compared_field->field,
                            'value' => ($cond->item_type == 'P2S' ? null : $cond->compared_value),
                        ];
                    }
                }
                $t_link->_src_conditions = $_src_conditions;
            }

        }

        $linkTable = $this->tableRepository->getTableByDB('table_field_links');
        $collection = collect([$t_link]);
        $collection = (new TableDataRowsRepository())->attachSpecialFields($collection, $linkTable, auth()->id(), ['conds']);
        return $collection->first();
    }

    /**
     * Build url for table with folders.
     * Accepted initial folder link to table.
     *
     * @param Table $table
     * @return string
     */
    public function getTablePath(Table $table)
    {
        $auth = app()->make(AuthUserSingleton::class);
        return $auth->getTableUrl($table->id);
    }

    /**
     * Get only table info
     *
     * @param $table_id
     * @return Table
     */
    public function getTable($table_id)
    {
        return $this->tableRepository->getTable($table_id);
    }

    /**
     * @param array $table_ids
     * @return mixed
     */
    public function getTables(array $table_ids)
    {
        return $this->tableRepository->getTables($table_ids);
    }

    /**
     * @param $hash
     * @return Table
     */
    public function getTableByHash($hash)
    {
        return $this->tableRepository->getTableByHash($hash);
    }

    /**
     * Get only table info
     *
     * @param $hash
     * @return mixed
     */
    public function getTableByViewHash($hash)
    {
        return $this->tableRepository->getTableByViewHash($hash);
    }

    /**
     * Get only table info by field_id
     *
     * @param $table_field_id
     * @return mixed
     */
    public function getTableByField($table_field_id)
    {
        return $this->tableFieldService->getTableByField($table_field_id);
    }

    /**
     * Get system tables with headers
     *
     * @param $user_id
     * @return array:
     * [
     *  //system table
     *  'ddl' => [
     *      //table info like : 'db_name' => 'value1',
     *      ...
     *      'fields' => [
     *          '0' => [// field settings for column 0 like : 'width' => '100' //],
     *          ...
     *      ],
     *  ],
     *  ...
     * ]
     */
    public function getSystemHeaders($user_id, array $only_part = [])
    {
        $arr = [];

        if (!$only_part || !empty($only_part['systables'])) {
            $sys_tables = $this->tableRepository->getSystemTables();
            $sys_tables->load([
                '_cond_formats',
                '_column_groups' => function ($q) {
                    $q->with('_fields:table_fields.id,field');
                }
            ]);

            if (!$user_id) {//standard settings if Guest
                $this->tableFieldService->loadFieldsWithStandardSettings($sys_tables, $user_id, false);
            }

            foreach ($sys_tables as $tb) {
                if ($user_id) {//unique settings if User/Admin
                    $tb->_fields = $this->tableFieldService->getWithSettings($tb, $user_id);
                }
                $tb->_is_owner = $tb->user_id === $user_id;
                $arr[$tb->db_name] = $tb;
            }
        }

        if (!$only_part || !empty($only_part['systables'])) {
            $arr['user_connections_data'] = (new UserConnRepository())->loadUserConns($user_id);
        }

        if (!$only_part || !empty($only_part['user_clouds_data'])) {
            $u_clouds = UserCloud::where('user_id', '=', $user_id)->get();
            foreach ($u_clouds as $cloud) {
                $cloud->__is_connected = !!$cloud->token_json;
            }
            $arr['user_clouds_data'] = $u_clouds;
        }

        if (!$only_part || !empty($only_part['available_tables'])) {
            $arr['available_tables'] = $this->getAvailableTables($user_id);
        }

        if (!$only_part || !empty($only_part['meta_app_settings'])) {
            $arr['meta_app_settings'] = AppSetting::all()->keyBy('key');
            $arr['formula_examples'] = FormulaHelper::all()->keyBy('formula');
        }

        if (!$only_part || !empty($only_part['unit_conversion'])) {
            $arr['unit_conversion'] = UnitConversion::all();
        }

        if (!$only_part || !empty($only_part['all_plans'])) {
            $arr['all_plans'] = Plan::with('_available_features')->get();
        }

        if (!$only_part || !empty($only_part['all_addons'])) {
            $arr['all_addons'] = Addon::all();
        }

        if (!$only_part || !empty($only_part['app_settings'])) {
            $arr['app_settings'] = AppSetting::all()->keyBy('key');
        }

        if (!$only_part || !empty($only_part['country_data'])) {
            $arr['country_data'] = CountryData::select(['id', 'currency_code', 'currency_symbol'])->get();
        }

        if (!$only_part || !empty($only_part['table_public_apps_data'])) {
            $corr_apps = CorrespApp::onlyPublicActive()
                ->with('_tables')
                ->get();
            $arr['table_public_apps_data'] = $corr_apps;
            $corr_apps = CorrespApp::onlyActive()
                ->with('_tables')
                ->get();
            $arr['table_apps_data'] = $corr_apps;
            $arr['corr_uis'] = CorrespStim3D::get(['id','top_tab','select','db_table']);
        }

        if (!$only_part || !empty($only_part['payment_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'payment_processing')->first();
            $arr['payment_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['json_import_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'general_json_import')->first();
            $arr['json_import_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['json_export_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'general_json_export')->first();
            $arr['json_export_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['eri_parser_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'eri_parser')->first();
            $arr['eri_parser_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['eri_writer_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'eri_writer')->first();
            $arr['eri_writer_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['da_loading_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'da_loading_ocr_parse')->first();
            $arr['da_loading_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['mto_dal_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'mto_dal_pdf_parse')->first();
            $arr['mto_dal_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['mto_geom_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'mto_geometry_pdf_parse')->first();
            $arr['mto_geom_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['ai_extractm_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'ai_extractm')->first();
            $arr['ai_extractm_app_id'] = $papp ? $papp->id : null;
        }
        if (!$only_part || !empty($only_part['smart_select_app_id'])) {
            $papp = CorrespApp::where('code', '=', 'smart_autoselect')->first();
            $arr['smart_select_app_id'] = $papp ? $papp->id : null;
        }

        if (!$only_part || !empty($only_part['system_tables_for_all'])) {
            $arr['system_tables_for_all'] = $this->service->system_tables_for_all;
            $arr['system_support_tables'] = $this->service->support_tables;
        }

        if (!$only_part || !empty($only_part['user_headers_attributes'])) {
            $arr['user_headers_attributes'] = (new UserHeaders())->avail_override_fields;
        }

        if (!$only_part || !empty($only_part['template_dcrs'])) {
            $arr['template_dcrs'] = (new TableDataRequestRepository())->getTemplates();
        }

        if (!$only_part || !empty($only_part['upload_file_formats'])) {
            $arr['upload_file_formats'] = (new UploadingFileFormatsRepository())->loadAttachmentsFormats();
        }

        if (!$only_part || !empty($only_part['countries_all'])) {
            $arr['countries_all'] = Country::all();
        }

        if (!$only_part || !empty($only_part['backend_env'])) {
            $arr['backend_env'] = [
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ];
        }

        if (!$only_part || !empty($only_part['shared_ddls'])) {
            $arr['shared_ddls'] = (new DDLRepository())->sharedDDLS();
        }


        return $arr;
    }

    /**
     * Get list of available tables for selected user.
     * Table is available if one of below is true:
     * - user is owner
     * - table shared for user with 'reference' functionality
     *
     * @param $user_id
     * @return mixed
     */
    public function getAvailableTables($user_id)
    {
        $auth = app()->make(AuthUserSingleton::class);
        $virepo = new TableViewRepository();

        $available_tables = $this->tableRepository->getAvailableTables($user_id);
        $available_tables->load([
            '_fields:id,table_id,field,name,input_type,ddl_id,f_type,is_showed,order,is_search_autocomplete_display',
            '_user:' . $this->service->onlyNames(),
            '_table_permissions' => function ($tp) {
                $tp->isActiveForUserOrVisitor();
                $tp->where('can_reference', 1);
            },
            '_views:id,table_id,user_id,name,is_active,hash,custom_path,is_system,parts_avail',
            '_ddls:id,table_id,name',
            '_ref_conditions:id,table_id,ref_table_id,name',
            '_row_groups:id,table_id,name',
            '_column_groups:id,table_id,name',
            '_cond_formats:id,table_id,name',
            '_saved_filters:id,table_id,user_id,name,related_colgroup_id',
        ]);

        foreach ($available_tables as &$tb) {
            if ($tb->user_id != $user_id && ($tb->_table_permissions->count() || $tb->is_system)) {
                $usr = $tb->_user;
                $tb->_referenced = $usr->first_name
                    ? ($usr->first_name . ' ' . $usr->last_name)
                    : $usr->username;
            } else {
                $tb->_referenced = '';
            }
            unset($tb->_table_permissions);
            $tb->__url = $auth->getTableUrl($tb->id);
            $tb->__visiting_url = $virepo->getVisitingUrl($tb->id, $tb->_views->first());
        }

        $available_tables->load('_table_permissions:id,table_id,name,is_system');

        return $available_tables->values();
    }

    /**
     * Get Correspondence Tables.
     *
     * @return mixed
     */
    public function getCorrespondenceTables()
    {
        $data_table_dbs = CorrespApp::ownedOrSubscribed()
            ->with('_tables')
            ->get()
            ->pluck('_tables')
            ->flatten()
            ->pluck('data_table');

        $corr_tables = $this->tableRepository->getTablesFromDB($data_table_dbs->toArray());

        $this->tableFieldService->loadFieldsWithStandardSettings($corr_tables, auth()->id(), false);

        return $corr_tables;
    }

    /**
     * Get Correspondence Tables.
     * @param array $row
     * @return mixed
     */
    public function getCorrespondenceUsedFields(array $row)
    {
        return CorrespField::where('correspondence_app_id', $row['correspondence_app_id'])
            ->where('correspondence_table_id', $row['correspondence_table_id'])
            ->select('data_field')
            ->get()
            ->pluck('data_field');
    }

    /**
     * Get Fees table (Plans and Addons prices)
     */
    public function getSettingsFees()
    {
        return [
            'all_plans' => Plan::with('_available_features')->get(),
            'all_addons' => Addon::all()
        ];
    }

    /**
     * Link table to folder.
     *
     * @param $table_id
     * @param $folder_id
     * @param string $type
     * @param string $structure
     * @param string $folder_path
     * @param int $user_id
     * @return array
     */
    public function createLink($table_id, $folder_id, $type = 'table', $structure = 'private', $folder_path, $user_id)
    {
        $link = $this->folderRepository->linkTable($table_id, $folder_id, $user_id, $type, $structure);

        $table = $this->tableRepository->getTable($table_id);
        $table->link = $table->_folder_links()
            ->where('folder_id', $folder_id)
            ->where('user_id', $user_id)
            ->first();

        $path = ($folder_path ? $folder_path : config('app.url') . "/data/");
        return $this->service->getTableObjectForMenuTree($table, 'link', $path, $folder_id);
    }

    /**
     * Delete table link from folder.
     *
     * @param int $link_id
     * @return array|bool|null
     */
    public function deleteLink($link_id)
    {
        return $this->tableRepository->deleteLink($link_id);
    }

    /**
     * Transfer table from the user`s folders tree to another user.
     *
     * @param Table $table
     * @param int $new_user_id
     * @param bool $move_to_transferred
     * @return mixed
     */
    public function transferTable(Table $table, $new_user_id, $move_to_transferred = true)
    {
        $sys_folder = $move_to_transferred
            ? $this->folderRepository->getSysFolder($new_user_id, 'TRANSFERRED')
            : (object)['id' => null];

        //new menutree user hash
        (new UserRepository())->newMenutreeHash($new_user_id);
        (new FolderService())->syncPrivateWithPublicShared();

        return $this->tableRepository->transferTable($table, $new_user_id, $sys_folder->id);
    }

    /**
     * Add Message to Table from the user to another user.
     *
     * @param Int $table_id
     * @param Int $from_user_id
     * @param Int $to_user_id
     * @param Int $to_user_group_id - nullable
     * @param String $message
     * @return mixed
     */
    public function addMessage(int $table_id, int $from_user_id, int $to_user_id, $to_user_group_id, string $message)
    {
        $msg = $this->tableRepository->addMessage($table_id, $from_user_id, $to_user_id, $to_user_group_id, $message);
        $msg->load(['_from_user', '_to_user', '_to_user_group']);
        return $msg;
    }

    /**
     * Get Messages by id/ids.
     *
     * @param $ids
     * @return mixed
     */
    public function getMessage($ids)
    {
        return $this->tableRepository->getMessage($ids);
    }

    /**
     * Delete Message.
     *
     * @param Int $message_id
     * @return mixed
     */
    public function deleteMessage(int $message_id)
    {
        return $this->tableRepository->deleteMessage($message_id);
    }

    /**
     * Move table or link to another folder.
     *
     * @param Table $table
     * @param User $user
     * @param int $link_id
     * @param int $folder_id
     * @return string
     */
    public function moveTable(Table $table, User $user, int $link_id = null, int $folder_id = null, int $position = null)
    {
        if ($link_id) {
            $this->tableRepository->updateLink($link_id, ['folder_id' => $folder_id]);
        } else {
            $this->tableRepository->insertLink($table->id, $folder_id);
        }
        $this->syncStructureOfShared([$table->id], $user->id);
        $this->tableRepository->updatePosition($table, $user->id, $folder_id, $position);

        //menutree is changed
        (new UserRepository())->newMenutreeHash($user->id);
        (new FolderService())->syncPrivateWithPublicShared();

        return $this->getTablePath($table);
    }

    /**
     * Delete shared links for all other Users (which were not changed by other Users)
     * to sync their structure with owner structure.
     *
     * @param array $table_ids
     * @param int $user_id
     * @return mixed
     */
    public function syncStructureOfShared(array $table_ids, int $user_id)
    {
        return $this->tableRepository->syncStructureOfShared($table_ids, $user_id);
    }

    /**
     * Toggle table in favorite for user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param bool $favorite
     * @param int|null $parent_id
     * @return bool
     */
    public function favoriteToggle($table_id, $user_id, $favorite, $parent_id)
    {
        return $this->tableRepository->favoriteToggle($table_id, $user_id, $favorite, $parent_id);
    }

    /**
     * Update table note for user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $notes
     * @return mixed
     */
    public function updateUserNote($table_id, $user_id, $notes)
    {
        return $this->tableRepository->updateUserNote($table_id, $user_id, $notes);
    }

    /**
     * Get Table 'Sum Usages'.
     *
     * @param array $data
     * @param Int $user_id
     * @return array
     */
    public function getSumUsages(array $data, $user_id)
    {
        $sum_usages_table = $this->tableRepository->getTableByDB('sum_usages');

        $page = $data['page'];
        $rows_per_page = $data['rows_per_page'];
        $data = array_merge($data, [
            'table_id' => $sum_usages_table->id,
        ]);
        $sum_usages_data = $this->tableDataRepository->getRows($data, $user_id);

        $all_rows = $sum_usages_data['rows'];

        foreach ($all_rows as &$row) {
            $row['tb_id'] = $row['id'];
            $row['table_id'] = $row['name'];
            $mult = $row['avg_row_length'] ?: ($row['num_columns'] * 16);
            $row['size'] = round(($row['num_rows'] * $mult) / (1024 * 1024), 3);

            $row['num_collaborators'] = $row['num_collaborators'] > 1 ? $row['num_collaborators'] : 1;
            $row['num_columns'] = $row['num_columns'] ? $row['num_columns'] : 0;
            $row['num_rows'] = $row['num_rows'] ? $row['num_rows'] : 0;

            $row['host'] = $row['_connection'] ? $row['_connection']['host'] : 'localhost';
            $row['database'] = $row['_connection'] ? $row['_connection']['db'] : env('DB_DATABASE');
            $row['db_name'] = $row['_connection'] ? $row['_connection']['table'] : $row['db_name'];
        }

        return [
            'filters' => $sum_usages_data['filters'],
            'rows' => $all_rows,
            'rows_count' => $sum_usages_data['rows_count']
        ];
    }

    /**
     * Get Table 'Fees'.
     *
     * @return array
     */
    public function getFees()
    {
        $plan_data = Plan::all()->toArray();
        $addon_data = Addon::all()->toArray();

        $all_rows = array_merge($plan_data, $addon_data);
        foreach ($all_rows as &$row) {
            $row['plan'] = !empty($row['plan_feature_id']) ? $row['name'] : null;
            $row['feature'] = !empty($row['plan_feature_id']) ? null : $row['name'];
        }

        return [
            'filters' => [],
            'rows' => $all_rows,
            'rows_count' => count($all_rows)
        ];
    }

    /**
     * Get Table 'Subscriptions'.
     *
     * @param array $data
     * @param Int $user_id
     * @return array
     */
    public function getSubscriptions(array $data, $user_id)
    {
        $subscription_table = $this->tableRepository->getTableByDB('user_subscriptions');

        $page = $data['page'];
        $rows_per_page = $data['rows_per_page'];
        $data = array_merge($data, [
            'table_id' => $subscription_table->id,
        ]);
        $subscription_data = $this->tableDataRepository->getRows($data, $user_id);

        $all_rows = $subscription_data['rows'];

        foreach ($all_rows as &$row) {
            $usr = $row['_user'];
            $key = $usr['renew'] == 'Yearly' ? 'per_year' : 'per_month';
            $add_bi = collect($row['_addons'])->where('code', '=', 'bi')->first();
            $add_map = collect($row['_addons'])->where('code', '=', 'map')->first();
            $add_request = collect($row['_addons'])->where('code', '=', 'request')->first();
            $add_alert = collect($row['_addons'])->where('code', '=', 'alert')->first();
            $add_kanban = collect($row['_addons'])->where('code', '=', 'kanban')->first();
            $add_gantt = collect($row['_addons'])->where('code', '=', 'gantt')->first();
            $add_email = collect($row['_addons'])->where('code', '=', 'email')->first();
            $add_calendar = collect($row['_addons'])->where('code', '=', 'calendar')->first();
            $add_twilio = collect($row['_addons'])->where('code', '=', 'twilio')->first();
            $add_tournament = collect($row['_addons'])->where('code', '=', 'tournament')->first();
            $add_simplemap = collect($row['_addons'])->where('code', '=', 'simplemap')->first();
            $add_grouping = collect($row['_addons'])->where('code', '=', 'grouping')->first();
            $add_report = collect($row['_addons'])->where('code', '=', 'report')->first();
            $add_ai = collect($row['_addons'])->where('code', '=', 'ai')->first();

            $row['user_id'] = $usr['id'];
            $row['_user_id_id'] = $usr['first_name'] ? $usr['first_name'] . ' ' . $usr['last_name'] : $usr['username'];
            $row['plan_id'] = $row['plan_code'];
            $row['add_bi'] = $add_bi ? 1 : 0;
            $row['add_map'] = $add_map ? 1 : 0;
            $row['add_request'] = $add_request ? 1 : 0;
            $row['add_alert'] = $add_alert ? 1 : 0;
            $row['add_kanban'] = $add_kanban ? 1 : 0;
            $row['add_email'] = $add_email ? 1 : 0;
            $row['add_calendar'] = $add_calendar ? 1 : 0;
            $row['add_twilio'] = $add_twilio ? 1 : 0;
            $row['add_tournament'] = $add_tournament ? 1 : 0;
            $row['add_simplemap'] = $add_simplemap ? 1 : 0;
            $row['add_grouping'] = $add_grouping ? 1 : 0;
            $row['add_ai'] = $add_ai ? 1 : 0;
            $row['add_report'] = $add_report ? 1 : 0;
            $row['add_gantt'] = $add_gantt ? 1 : 0;
            $row['avail_credit'] = $usr['avail_credit'];
            $row['renew'] = $usr['renew'];
            $row['recurrent_pay'] = $usr['recurrent_pay'];
        }

        return [
            'filters' => $subscription_data['filters'],
            'rows' => $all_rows,
            'rows_count' => $subscription_data['rows_count']
        ];
    }

    /**
     * Get MapIcons for selected Table and Field.
     *
     * @param Table $table
     * @param TableField $field
     * @return array
     */
    public function getMapIcons(Table $table, $field)
    {
        $res = [];

        if ($field) {
            $map_icons = $field->_map_icons;
            $values = $this->tableDataRepository->getDistinctiveField($table, $field);
            array_push($values, 'Default');

            foreach ($values as $value) {
                $icon = $map_icons->where('row_val', '=', $value)->first();
                $res[] = [
                    'row_val' => $value,
                    'icon_path' => $icon ? $icon->icon_path : '',
                    'height' => $icon ? $icon->height : '',
                    'width' => $icon ? $icon->width : '',
                    'table_field_id' => $icon ? $icon->table_field_id : '',
                ];
            }
        }

        return $res;
    }

    /**
     * Add Map Icon
     *
     * @param Table $table
     * @param TableField $field
     * @param array $data : [
     *  table_field_id: int,
     *  row_val: string,
     *  height: int,
     *  width: int
     * ]
     * @param $upload_file
     * @return string
     */
    public function addMapIcon(Table $table, TableField $field, array $data, $upload_file)
    {
        $filePath = $this->fileRepository->getStorageTable($table) . "/";
        $filePath .= "map_icons/" . $field->field . "/";

        $fileName = preg_replace('/[\s\?&]/i', '_', $upload_file->getClientOriginalName());
        $upload_file->storeAs("public/" . $filePath, $fileName);

        $data = array_merge($data, ['icon_path' => $filePath . $fileName]);

        $this->tableRepository->addMapIcon($data);

        return [
            'path' => $filePath . $fileName,
            'table_field_id' => $field->id
        ];
    }

    /**
     * Update Map Icon
     *
     * @param array $data : [
     *  table_field_id: int,
     *  row_val: string,
     *  height: int,
     *  width: int
     * ]
     * @return mixed
     */
    public function updateMapIcon(array $data)
    {
        return $this->tableRepository->updateMapIcon($data);
    }

    /**
     * Delete Map Icon
     *
     * @param $field_id
     * @param $row_val
     * @return mixed
     */
    public function delMapIcon($field_id, $row_val)
    {
        return $this->tableRepository->delMapIcon($field_id, $row_val);
    }

    /**
     * Enable filters for table if in request present some conditions for not enabled filters.
     *
     * @param Table $table
     * @param $fields
     * @param $user_id
     */
    public function autoEnableFilters(Table $table, $fields, $user_id)
    {
        $table->loadMissing('_fields');
        foreach ($fields as $fld_key => $fld_val) {
            $tb_fld = $table->_fields->where('field', '=', $fld_key)->first();
            if ($tb_fld && !$tb_fld->filter) {
                if ($table->user_id != $user_id) {
                    UserHeaders::updateOrCreate([
                        'table_field_id' => $tb_fld->id,
                        'user_id' => $user_id,
                    ], [
                        'filter' => 1
                    ]);
                } else {
                    $tb_fld->update([
                        'filter' => 1
                    ]);
                }
            }
        }
    }

    /**
     * Get Table Rows count.
     *
     * @param Table $table
     * @return int
     */
    public function calcTableCount(Table $table)
    {
        $conn = $this->service->getConnectionForTable($table);
        if ($table->db_name == 'sum_usages') {
            return $conn->table('tables')->where('is_system', 0)->count();
        } else {
            return $conn->table($table->db_name)->count();
        }
    }

    /**
     * Update or Create shared table alias for selected user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $name
     * @return mixed
     */
    public function renameSharedTable(int $table_id, int $user_id, string $name)
    {
        return $this->tableRepository->renameSharedTable($table_id, $user_id, $name);
    }

    /**
     * Fill TableFields by tooltips
     *
     * @param Table $table
     * @param $options
     * @return array
     */
    public function parseTooltips(Table $table, $user_id, $options)
    {
        $this->tableFieldService->loadFieldsWithStandardSettings($table, $user_id, false);

        $v_idx = 0;
        foreach ($table->_fields as $fld) {
            if ($v_idx < count($options)) {
                $fld->tooltip = trim($options[$v_idx]);
                $fld->tooltip = preg_replace('/[,;]$/i', '', $fld->tooltip);
                $fld->save();
                $v_idx++;
            } else {
                break;
            }
        }

        return $table->_fields->toArray();
    }

    /**
     * @param int $table_id
     * @param array $row
     * @return mixed
     */
    public function infoRow(int $table_id, array $row)
    {
        $table = $this->tableRepository->getTable($table_id);
        $with_attachs = collect([(object)$row]);
        $with_attachs = (new TableDataRowsRepository())->attachSpecialFields($with_attachs, $table, null, ['users', 'refs', 'groups']);
        return $with_attachs->first();
    }
}