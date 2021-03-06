<?php


namespace Vanguard\Services\Tablda;


use Exception;
use File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\ExcelWrapper;
use Vanguard\Classes\Tablda;
use Vanguard\Jobs\ImportTableData;
use Vanguard\Jobs\RecalcTableFormula;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserConnection;
use Vanguard\Modules\Airtable\AirtableApi;
use Vanguard\Modules\CloudBackup\DropBoxApiModule;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Modules\CloudBackup\OneDriveApiModule;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\CopyTableRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\User;

class ImportService
{
    private $importRepository;
    private $tableRepository;
    private $copyTableRepository;
    private $fieldRepository;
    private $tableDataService;
    private $folderRepository;
    private $permissionsService;
    private $tableViewRepository;
    private $fileRepository;
    private $htmlservice;
    private $service;

    /**
     * ImportService constructor.
     */
    public function __construct()
    {
        $this->importRepository = new ImportRepository();
        $this->tableRepository = new TableRepository();
        $this->copyTableRepository = new CopyTableRepository();
        $this->fieldRepository = new TableFieldRepository();
        $this->tableDataService = new TableDataService();
        $this->folderRepository = new FolderRepository();
        $this->permissionsService = new TablePermissionService();
        $this->tableViewRepository = new TableViewRepository();
        $this->fileRepository = new FileRepository();
        $this->service = new HelperService();
        $this->htmlservice = new HtmlXmlService();
    }

    /**
     * Save Copy Folder with SubFolders to another User.
     *
     * @param $jstree_folder
     * @param $new_user_id
     * @return array|mixed
     */
    public function saveCopyFolderWithSubs($jstree_folder, $new_user_id)
    {
        $sys_folder = $this->folderRepository->getSysFolder($new_user_id, 'TRANSFERRED');
        return $this->copyFolderAndSubfolder($jstree_folder, $new_user_id, $sys_folder->id);
    }

    /**
     * Copy Folder with Sub-Folders and Tables to another User.
     *
     * @param array $jstree_folder
     * @param int $new_user_id
     * @param int $parent_folder_id
     * @param string $new_name
     * @param bool $overwrite
     * @param bool $with_links
     * @return array
     * @throws Exception
     */
    public function copyFolderAndSubfolder(array $jstree_folder, int $new_user_id, int $parent_folder_id, string $new_name = '', bool $overwrite = false, bool $with_links = false): array
    {
        $fld_name = $new_name ?: $jstree_folder['init_name'] ?? ($jstree_folder['text'] ?? '');
        if ($tested = $this->folderRepository->testNameOnLvl($fld_name, $parent_folder_id, $new_user_id)) {
            if (!$overwrite) {
                return ['error' => true, 'msg' => 'Already Copied.', 'already_copied' => true];
            } else {
                (new FolderService())->deleteFolderWithSubs($tested->id, $new_user_id);
            }
        }

        //copy folders and tables
        $new_fld_id = $this->copyFolderTreeForOthers($jstree_folder, $new_user_id, $parent_folder_id, $with_links);

        //new menutree user hash
        (new UserRepository())->newMenutreeHash($new_user_id);

        return ['new_folder_id' => $new_fld_id];
    }

    /**
     * Copy Folder with Sub-Folders and Tables to another User.
     *
     * @param array $jstree_folder
     * @param int $new_user_id
     * @param int $parent_folder_id
     * @param bool $with_links
     * @return false|int|null
     * @throws Exception
     */
    private function copyFolderTreeForOthers(array $jstree_folder, int $new_user_id, int $parent_folder_id, bool $with_links = false)
    {
        $created_fld_id = null;
        if ($this->jsonObjectIsSelected($jstree_folder)) {

            $created_fld_id = $this->getOrCopyFolder($jstree_folder, $parent_folder_id, $new_user_id);

            if ($created_fld_id) {

                foreach ($jstree_folder['children'] as &$inner) {
                    $type = $inner['li_attr']['data-type'] ?? '';
                    $is_table = $type == 'table' || ($with_links && $type == 'link');
                    if ($is_table && $this->jsonObjectIsSelected($inner)) {

                        $table = $this->tableRepository->getTable($inner['li_attr']['data-object']['id'] ?? 0);
                        $tb_settings = !empty($inner['li_attr']['data-copy-settings']) ? $inner['li_attr']['data-copy-settings'] : null;

                        $res = $this->copyTable($table, $new_user_id, $created_fld_id, $tb_settings);
                        $this->copyTableRepository->copied_table_compares[$table->id] = $res['new_table']->id;
                        $this->copyTableSettings($table, $new_user_id, $res['new_table'], $tb_settings);

                    }
                    if ($type == 'folder' && $this->jsonObjectIsSelected($inner)) {
                        $this->copyFolderTreeForOthers($inner, $new_user_id, $created_fld_id);
                    }
                }

            }
        }
        return $created_fld_id;
    }

    /**
     * JS-Tree Object is selected or its children.
     *
     * @param $jstree_folder
     * @return bool
     */
    public function jsonObjectIsSelected($jstree_folder)
    {
        $selected = $jstree_folder['state']['selected'] ?? false;
        if (!$selected) {
            foreach ($jstree_folder['children'] as $inner) {
                $selected = $this->jsonObjectIsSelected($inner);
                if ($selected) {
                    break;
                }
            }
        }
        return $selected;
    }

    /**
     * Copy folder.
     *
     * @param $jstree_folder
     * @param $parent_folder_id
     * @param $new_user_id
     * @return int
     */
    public function getOrCopyFolder(array &$jstree_folder, $parent_folder_id, $new_user_id)
    {
        $folder_id = $jstree_folder['li_attr']['data-copied-folder-id'] ?? false;

        if (!$folder_id) {
            $old = $jstree_folder['li_attr']['data-object'];
            $folder_id = $this->copyFolder($old, $parent_folder_id, $new_user_id);
            $jstree_folder['li_attr']['data-copied-folder-id'] = $folder_id;
        }

        return $folder_id;
    }

    /**
     * Copy folder.
     *
     * @param $old
     * @param $parent_folder_id
     * @param $new_user_id
     * @return int
     */
    private function copyFolder(array $old, $parent_folder_id, $new_user_id)
    {
        $folder = $this->folderRepository->insertFolder(
            $parent_folder_id,
            $new_user_id,
            $old['name'],
            'private',
            [
                'in_shared' => $old['in_shared']
            ]
        );

        //copy Folder Icon.
        if ($old['icon_path']) {
            $new_icon_path = preg_replace('/\/\d+_/i', '/' . $folder->id . '_', $old['icon_path']);
            $this->folderRepository->updateFolder($folder->id, ['icon_path' => $new_icon_path], $new_user_id);
            //copy folder icon in Storage
            $old_path = storage_path('app/public/') . $old['icon_path'];
            if (File::exists($old_path)) {
                $new_path = storage_path('app/public/') . $new_icon_path;
                File::copy($old_path, $new_path);
            }
        }

        return $folder->id;
    }

    /**
     * Get Initial Columns From Table to Add them to another Table.
     *
     * @param Table $table
     * @param $new_user_id
     * @param int $new_folder_id
     * @param null $settings
     * @param bool $overwrite
     * @param bool $visitor
     * @return array
     */
    public function copyTable(Table $table, $new_user_id, int $new_folder_id, $settings = null, $overwrite = false, $visitor = false)
    {
        $data = $this->prepareData($table->only(['name', 'rows_per_page', 'notes']), $new_user_id);

        if ($tested = $this->tableRepository->testNameOnLvl($data['name'], $new_folder_id, $new_user_id)) {
            if (!$overwrite) {
                return ['error' => true, 'msg' => 'Already Copied.', 'already_copied' => true];
            } else {
                $this->deleteTable($tested);
            }
        }

        //Copy table and data in DB
        $copyAvails = $visitor ? $this->copyAvails($table, null) : [];
        $with_data = is_null($settings) || !empty($settings['data']);
        $this->copyTableInDB($table, $copyAvails, $data['db_name'], $with_data);

        //copy in DB_SYS
        $initial_columns = $this->getInitialColumns($table, $copyAvails['columns'] ?? []);
        $result = $this->createTable($data, $new_user_id, $new_folder_id, '', $initial_columns);
        if ($result['error']) {
            return $result;
        }

        $new_table = $this->tableRepository->getTable($result['table_id']);

        //copy attachments
        if (is_null($settings) || !empty($settings['data_attach'])) {
            $this->fileRepository->copyTableAttachments($table, $new_table);
            (new TableDataRepository())->updateCopiedAttachPaths($new_table);
        }

        return [
            'result' => $result,
            'new_table' => $new_table
        ];
    }

    /**
     * Prepare Data for Create table.
     *
     * @param array $data
     * @param $user_id
     * @return array
     */
    public function prepareData(array $data, $user_id)
    {
        if (empty($data['db_name'])) {
            //$data['name'] = preg_replace('/[^\w\d-_ ]/i', '', $data['name']);
            $db_name = strtolower($data['name']);
            $db_name = preg_replace('/ /i', '_', substr($db_name, 0, 50));
            $db_name = preg_replace('/[^\w\d_]/i', '', $db_name) . date('YmdHis');
            $data['db_name'] = $db_name;
        }
        $data['user_id'] = $user_id;
        $data['num_rows'] = 0;
        $data['num_columns'] = 5;
        $data['num_collaborators'] = 0;
        return $data;
    }

    /**
     * Delete user`s table
     *
     * @param Table $table
     * @return array|bool|null
     */
    public function deleteTable(Table $table)
    {
        $this->fileRepository->deleteAllAttachments($table);
        $error = $this->importRepository->deleteTable($table->db_name);
        if ($error) {
            $res = ['error' => true, 'msg' => $error];
        }
        $deleted = $this->tableRepository->deleteTable($table->id);
        if (!$deleted) {
            $res = ['error' => true];
        }

        //menutree is changed
        (new UserRepository())->newMenutreeHash($table->user_id);

        return (isset($res) ? $res : ['error' => false]);
    }

    /**
     * @param Table $table
     * @param int|null $user_id
     * @return array
     */
    protected function copyAvails(Table $table, int $user_id = null)
    {
        $permission = TableRights::permissions($table);
        return [
            'columns' => $permission->view_fields->merge((new HelperService())->system_fields)->unique()->toArray(),
            'rows' => (new TableDataRepository())->getRowsColumn($table, $permission)->toArray(),
        ];
    }

    /**
     * @param Table $table
     * @param array $copyAvails
     * @param string $db_name
     * @param bool $with_data
     */
    protected function copyTableInDB(Table $table, array $copyAvails, string $db_name, bool $with_data)
    {
        //config connection
        $db = $this->service->getConnectionForTable($table);

        $columns = $copyAvails
            ? implode(',', $copyAvails['columns'])
            : '*';

        if ($with_data) {
            $rows = $copyAvails
                ? '`id` IN (' . implode(',', $copyAvails['rows']) . ')'
                : 'true';
        } else {
            $rows = 'false';
        }

        //copy in DB_DATA
        $db->select('CREATE TABLE `' . $db_name . '` SELECT ' . $columns . ' FROM `' . $table->db_name . '` WHERE ' . $rows);
        //create 'id' as primary key
        $db->select('UPDATE `' . $db_name . '` SET `created_on` = now(), `modified_on` = now()');
        $db->select('ALTER TABLE `' . $db_name . '` CHANGE `id` `id` INT(10) AUTO_INCREMENT, add PRIMARY KEY (`id`)');
    }

    /**
     * Get Initial Columns From Table to Add them to another Table.
     *
     * @param Table $table
     * @param array $columns
     * @return mixed
     */
    public function getInitialColumns(Table $table, array $columns)
    {
        $table->load([
            '_fields' => function ($f) {
                $f->joinOwnerHeader();
            }
        ]);

        $init_fields = [];
        foreach ($table->_fields as $fld) {
            if (!$columns || in_array($fld->field, $columns)) {
                $init_fields[] = $fld->only((new TableField())->getFillable());
            }
        }

        return $init_fields;
    }

    /**
     * Create table with system fields
     *
     * @param array $data
     * @param $user_id
     * @param $folder_id
     * @param string $folder_path
     * @param array $initial_columns
     * @return array
     */
    public function createTable(array $data, $user_id, $folder_id, $folder_path = '', $initial_columns = [])
    {
        $data['name'] = Tablda::safeName($data['name']);
        if ($this->tableRepository->testNameOnLvl($data['name'], $folder_id, $user_id)) {
            return ['error' => true, 'msg' => 'Node Name Taken. Enter a Different Name.'];
        }

        $data = $this->prepareData($data, $user_id);

        if (empty($initial_columns)) {
            //create table in the DataBase
            $error = $this->importRepository->createTableWithColumns($data['db_name']);
            if ($error) {
                return ['error' => true, 'msg' => $error];
            }
        }

        //add record to 'tables'
        $table = $this->tableRepository->addTable($data);

        //add table fields
        $this->fieldRepository->addFieldsForCreatedTable($table->id, $initial_columns ?: $this->getSystemColumns());

        //link to folder
        $this->folderRepository->linkTable($table->id, $folder_id, $user_id);

        //add right for 'Visitor' if table will be published
        $this->permissionsService->addSystemRights($table->id, 0, $initial_columns ? $initial_columns : []);
        $this->tableViewRepository->addSys($table);

        $table->link = $table->_link_initial_folder;
        $path = ($folder_path ? $folder_path : config('app.url') . "/data/");

        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        return [
            'error' => false,
            'path' => ($folder_path ? $folder_path : config('app.url') . "/data/") . $table->name,
            'table_id' => $table->id,
            'table_object' => $this->service->getTableObjectForMenuTree($table, 'table', $path, $folder_id)
        ];
    }

    /**
     * Get System Columns for Table
     *
     * @return array
     */
    private function getSystemColumns()
    {
        return [
            [
                'field' => 'id',
                'name' => 'ID',
                'f_type' => 'Auto Number',
                'input_type' => 'Auto',
                'is_showed' => 0,
                'show_zeros' => 0,
                'order' => 1
            ],
            [
                'field' => 'created_by',
                'name' => 'Created By',
                'f_type' => 'User',
                'input_type' => 'Auto',
                'is_showed' => 0,
                'show_zeros' => 0,
                'order' => 0
            ],
            [
                'field' => 'created_on',
                'name' => 'Created On',
                'f_type' => 'Date Time',
                'input_type' => 'Auto',
                'is_showed' => 0,
                'show_zeros' => 0,
                'order' => 0
            ],
            [
                'field' => 'modified_by',
                'name' => 'Modified By',
                'f_type' => 'User',
                'input_type' => 'Auto',
                'is_showed' => 0,
                'show_zeros' => 0,
                'order' => 0
            ],
            [
                'field' => 'modified_on',
                'name' => 'Modified On',
                'f_type' => 'Date Time',
                'input_type' => 'Auto',
                'is_showed' => 0,
                'show_zeros' => 0,
                'order' => 0
            ],
        ];

    }

    /**
     * Get Initial Columns From Table to Add them to another Table.
     *
     * @param Table $table
     * @param $new_user_id
     * @param Table $new_table
     * @param null $settings
     * @return bool
     * @throws Exception
     */
    public function copyTableSettings(Table $table, $new_user_id, Table $new_table, $settings = null)
    {
        $errors = [];
        $new_table->update($table->getCopyAttrs());

        try {
            $this->copyTableRepository->copyHeaderSettings($table, $new_table);
        } catch (Exception $e) {
            $errors[] = 'Headers Settings';
        }

        //NOTE: shouldn't change order

        if (is_null($settings) || !empty($settings['grouping_rcs'])) {
            $new_user = User::where('id', $new_user_id)->first();
            try {
                $this->copyTableRepository->copyReferenceConditions($table, $new_table, $new_user);
            } catch (Exception $e) {
                $errors[] = 'Reference Conditions';
            }
        }

        if (is_null($settings) || !empty($settings['grouping_rows'])) {
            try {
                $this->copyTableRepository->copyRowGroups($table, $new_table);
            } catch (Exception $e) {
                $errors[] = 'Row Groups';
            }
        }
        if (is_null($settings) || !empty($settings['grouping_columns'])) {
            try {
                $this->copyTableRepository->copyColumnGroups($table, $new_table);
            } catch (Exception $e) {
                $errors[] = 'Column Groups';
            }
        }

        if (is_null($settings) || !empty($settings['ddls'])) {
            try {
                $this->copyTableRepository->copyDDLs($table, $new_table);
            } catch (Exception $e) {
                $errors[] = 'DDLs';
            }
        }
        if (is_null($settings) || !empty($settings['basics'])) {
            try {
                $this->copyTableRepository->copyBasics($table, $new_table);
            } catch (Exception $e) {
                $errors[] = 'Basics';
            }
        }
        if (is_null($settings) || !empty($settings['links'])) {
            try {
                $this->copyTableRepository->copyLinks($table, $new_table);
            } catch (Exception $e) {
                $errors[] = 'Links';
            }
        }
        if (is_null($settings) || !empty($settings['cond_formats'])) {
            try {
                $this->copyTableRepository->copyCondFormats($table, $new_table, $new_user_id);
            } catch (Exception $e) {
                $errors[] = 'Cond Formats';
            }
        }

        try {
            $this->copyTableRepository->copyMapIconsAndCharts($table, $new_table);
        } catch (Exception $e) {
            $errors[] = 'Map Icons And Charts';
        }

        if ($errors) {
            throw new Exception(implode(', ', $errors) . ' are not copied!', 1);
        }
        return true;
    }

    /**
     * Save Copy Table to another User.
     *
     * @param Table $table
     * @param $new_user_id
     * @param null $settings
     * @param bool $overwrite
     * @param bool $visitor
     * @return array|mixed
     */
    public function saveCopyTable(Table $table, $new_user_id, $settings = null, $overwrite = false, $visitor = false)
    {
        $sys_folder = $this->folderRepository->getSysFolder($new_user_id, 'TRANSFERRED');

        $res = $this->copyTable($table, $new_user_id, $sys_folder->id, $settings, $overwrite, $visitor);
        if (!empty($res['error'])) {
            return $res;
        }

        $this->copyTableSettings($table, $new_user_id, $res['new_table'], $settings);

        //new menutree user hash
        (new UserRepository())->newMenutreeHash($new_user_id);

        return $res['result'];
    }

    /**
     * Modify columns for user`s table
     *
     * @param Table $table
     * @param array $data :
     * [
     *  columns: [
     *      status: string,
     *      name: string,
     *      field: string,
     *      col: string,
     *      f_type: string,
     *      f_size: float,
     *      f_default: string,
     *      f_required: int(0|1),
     *  ]
     * ]
     * @return array
     */
    public function modifyTable(Table $table, array $data)
    {
        //if needs to replace table with columns -> prepare to do it.
        if (
            in_array($data['import_type'], ['csv', 'mysql', 'remote', 'paste', 'web_scrap', 'g_sheets', 'table_ocr', 'airtable_import', 'chart_export'])
            &&
            $data['import_action'] === 'new'
        ) {
            $table = $this->prepareTableForReplace($table, $data);
            if (!empty($table['error'])) {
                return $table;
            }
        }

        //prepare 'columns' and build db_name for columns from provided column names.
        foreach ($data['columns'] as $idx => &$col) {
            $col['name'] = $col['name'] ?: 'col_' . $idx;
            $col['field'] = empty($col['field']) ? $this->fieldRepository->getDbField($col['name'], $idx) : $col['field'];
            $col['f_size'] = empty($col['f_size']) ? 255 : floatval(str_replace(',', '.', $col['f_size']));
            if ($col['f_type'] === 'Boolean' && empty($col['f_required'])) {
                $col['f_required'] = 0;
            }
        }

        //modify table in the DataBase
        $error = $this->importRepository->modifyTableColumns($table, $data);
        if ($error) {
            return ['error' => true, 'msg' => $error];
        }

        //modify table fields
        $this->fieldRepository->changeFieldsForModifiedTable($table, $data['columns']);

        //sync saved views
        $this->tableViewRepository->syncTableViews($table->id, $data['columns']);

        //work with data (import or remove)
        if ($table->_fields()->notSystemFields()->count()) {
            $job_id = $this->importData($table, $data);
        } else {
            $reqst = [ 'table_id' => $table->id, 'page' => 1, 'rows_per_page' => 0, ];
            $this->tableDataService->deleteAllRows($table, $reqst, $table->user_id);
        }

        $this->tableRepository->onlyUpdateTable($table->id, [
            'source' => $data['import_type'],
            'connection_id' => in_array($data['import_type'], ['mysql', 'remote']) ? $data['mysql_settings']['connection_id'] : null,
            'num_columns' => count($this->fieldRepository->getFieldsForTable($table->id)),
        ]);

        return [
            'error' => false,
            'msg' => '',
            'job_id' => $job_id ?? 0,
            'new_id' => $table->id,
        ];
    }

    /**
     * @param Table $table
     * @param array $data
     * @return int
     */
    protected function importData(Table $table, array $data): int
    {
        //import data
        if (in_array($data['import_type'], ['csv', 'mysql', 'reference', 'paste', 'g_sheets', 'web_scrap', 'table_ocr', 'airtable_import', 'chart_export'])) {
            $job = Import::create([
                'file' => "app/tmp_import/" . $data['csv_settings']['filename'],
                'status' => 'initialized'
            ]);
            dispatch(new ImportTableData($table, $data, auth()->user(), $job->id));
        } else {
            if ($table->num_rows > $this->service->recalc_table_formulas_job) {
                $recalc = Import::create([
                    'file' => '',
                    'status' => 'initialized'
                ]);
                dispatch(new RecalcTableFormula($table, auth()->id(), $recalc->id));
                Session::flash('recalc_id', $recalc->id);
            } else {
                //recalc all formulas if table has them
                $this->tableDataService->recalcTableFormulas($table);
            }
        }
        return isset($job) ? $job->id : 0;
    }

    /**
     * Delete table and create again for Replacing
     *
     * @param Table $table
     * @param array $data
     * @return Table|array|bool|null
     */
    public function prepareTableForReplace(Table $table, array $data)
    {
        $folder = $table->_folder_links()->where('type', '=', 'table')->first();
        $folder_id = $folder ? $folder->folder_id : null;
        $user_id = $table->user_id;
        $tb = $table->only([
            'db_name', 'name', 'rows_per_page', 'notes', 'import_web_scrap_save', 'import_gsheet_save',
            'import_airtable_save', 'import_table_ocr_save', 'import_csv_save', 'import_mysql_save', 'import_paste_save'
        ]);

        $resp = $this->deleteTable($table);
        if (!empty($resp['error'])) {
            return $resp;
        }

        $resp = $this->createTable($tb, $user_id, $folder_id);
        if (!empty($resp['error'])) {
            return $resp;
        }

        foreach ($data['columns'] as &$col) {
            $col['status'] = 'add';
        }

        return Table::where('id', '=', $resp['table_id'])->first();
    }

    /**
     * Get fields from Pasted Data for import it as table
     *
     * @param $pasted_data
     * @param $settings
     * @return array
     */
    public function getFieldsFromPaste(string $pasted_data, array $settings)
    {
        $file_hash = Uuid::uuid4() . '.txt';
        if (!Storage::put("pasted/" . $file_hash, $pasted_data)) {
            return ['error' => "File accessing error!"];
        }

        //parse $pasted_data
        $headers = [];
        $strings = preg_split('/\r\n|\r|\n/', $pasted_data);
        foreach ($strings as $row_idx => $row) {

            $row_cells = $this->service->pastedDataParser($row);

            if (!count($headers)) {
                $headers = $this->makeHeaders($row_cells);
            }

            if (count($row_cells) > count($headers)) {
                $row_cells = array_slice($row_cells, 0, count($headers));
            }

            if ($row_idx == 0 && $settings['f_header']) {
                foreach ($row_cells as $key => $val) {
                    $headers[$key]['name'] = $val;
                }
            }

            //auto fill 'f_size' from columns
            foreach ($row_cells as $key => $val) {
                if (strlen((string)$val) > $headers[$key]['f_size']) {
                    $headers[$key]['f_size'] = strlen((string)$val);
                }
            }
        }

        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
            'file_hash' => $file_hash
        ];
    }

    /**
     * @param array $row
     * @param bool $use_val
     * @param array $types
     * @param array $specials
     * @return array
     */
    protected function makeHeaders(array $row, bool $use_val = false, array $types = [], array $specials = [])
    {
        $headers = [];
        foreach ($row as $key => $val) {
            $headers[$key]['name'] = $use_val ? ($val ?: 'col_' . $key) : 'col_' . $key;
            $headers[$key]['status'] = 'add';
            $headers[$key]['field'] = ($specials['autofield']??'') ? ('field_' . $key) : '';
            $headers[$key]['col'] = ($specials['autocol']??'') ? $key : null;
            $headers[$key]['f_type'] = $types[$key] ?? 'String';
            $headers[$key]['f_size'] = 64;
            $headers[$key]['f_default'] = '';
            $headers[$key]['f_required'] = 0;
        }
        return $headers;
    }

    /**
     * @param array $headers
     * @param array $rows
     * @return array
     */
    public function datasForChartExport(array $headers, array $rows): array
    {
        return [
            'import_type' => 'chart_export',
            'import_action' => 'new',
            'columns' => $this->makeHeaders($headers, true, [], ['autofield'=>true, 'autocol'=>true]),
            'chart_rows' => $rows,
            'csv_settings' => [
                'filename' => ''
            ]
        ];
    }

    /**
     * Get fields from Google Sheets for import it as table
     *
     * @param $g_sheets_id
     * @param $g_sheets_page
     * @param $settings
     * @return array
     */
    public function getFieldsFromGSheet(string $g_sheets_id, string $g_sheets_page, array $settings)
    {
        try {
            $user = auth()->user();
            $token_json = $user
                ? $user->_clouds()->where('cloud', 'Google')->whereNotNull('token_json')->first()
                : null;
            $token_json = $token_json ? $token_json->gettoken() : null;
            $strings = $this->service->parseGoogleSheet($g_sheets_id, $g_sheets_page, $token_json);
        } catch (Exception $e) {
            return ['error' => (json_decode($e->getMessage(), 1))['error']['message'] ?? $e->getMessage()];
        }

        $headers = [];
        foreach ($strings as $row_idx => $row) {

            if (!count($headers)) {
                $headers = $this->makeHeaders($row);
            }

            if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            if ($row_idx == 0 && $settings['f_header']) {
                foreach ($row as $key => $val) {
                    $headers[$key]['name'] = $val;
                }
            }

            //auto fill 'f_size' from columns
            foreach ($row as $key => $val) {
                if (strlen((string)$val) > $headers[$key]['f_size']) {
                    $headers[$key]['f_size'] = strlen((string)$val);
                }
            }
        }

        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
    }

    /**
     * @param $url
     * @param $xpath
     * @param $query
     * @param $index
     * @param $web_headers
     * @return array
     */
    public function getFieldsFromHtml($url, $xpath, $query, $index, $web_headers)
    {
        if ($xpath) {
            $row = $this->htmlservice->parseXpathHtml($url, $xpath);
        } else {
            $row = $this->htmlservice->parsePageHtml($url, $query, $index);
        }
        $headers = $this->makeHeaders($row, !!$web_headers);
        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
    }

    /**
     * @param array $xml_settings
     * @return array
     */
    public function getFieldsFromXml(array $xml_settings)
    {
        $row = $this->htmlservice->parseXmlPage($xml_settings, false);
        $headers = $this->makeHeaders($row, true);
        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
    }

    /**
     * @param UserApiKey $key
     * @param string $table_name
     * @param string $fromtype
     * @return array
     * @throws Exception
     */
    public function getFieldsFromAirtable(UserApiKey $key, string $table_name, string $fromtype): array
    {
        $api = new AirtableApi($key->decryptedKey(), $key->air_base, $fromtype);
        $air_columns = $api->tableFields($table_name);
        $headers = $this->makeHeaders(array_keys($air_columns), true, array_values($air_columns));
        foreach ($headers as &$hdr) {
            $hdr['_source_type'] = $api->typeConvertToAir($hdr['f_type']);
        }
        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
    }

    /**
     * @param UserApiKey $key
     * @param string $table_name
     * @param string $col_name
     * @param string $fromtype
     * @return array
     * @throws Exception
     */
    public function getColValuesFromAirtable(UserApiKey $key, string $table_name, string $col_name, string $fromtype): array
    {
        $api = new AirtableApi($key->decryptedKey(), $key->air_base, $fromtype);
        return $api->fetchColValues($table_name, $col_name);
    }

    /**
     * @param $url
     * @return array
     */
    public function preloadHtmlXml($url)
    {
        return ['elems' => $this->htmlservice->preloadElements($url)];
    }

    /**
     * Get fields from CSV file for import it as table
     *
     * @param $upload_file
     * @param $file_link
     * @param array $data
     * @return array
     */
    public function getFieldsFromCSV($upload_file, $file_link, array $data)
    {
        $originalName = $upload_file ? $upload_file->getClientOriginalName() : $file_link;
        $ext = $data['extension'] ?? strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($upload_file) {
            $tmp_csv = time() . "_" . rand() . "." . $ext;
            $upload_file->storeAs('tmp_import', $tmp_csv);
        } else {
            $tmp_csv = time() . "_" . rand() . "." . $ext;
            $llink = preg_match('/^http/i', $file_link) ? $file_link : storage_path('app/tmp_import/' . $file_link);
            if (!file_exists($llink) || !Storage::put("tmp_import/" . $tmp_csv, file_get_contents($llink))) {
                return ['error' => "File accessing error!"];
            }
        }

        $xls_sheets = [];
        if ($ext == 'csv') {
            $headers = $this->loadCsv($data, $tmp_csv);
        } else {
            $headers = $this->loadExcel($data, $tmp_csv);
            $xls_sheets = ExcelWrapper::getSheets($tmp_csv);
        }

        return [
            'headers' => $headers,
            'csv_fields' => array_pluck($headers, 'name'),
            'csv_file' => $tmp_csv,
            'xls_sheets' => $xls_sheets,
        ];
    }

    /**
     * @param array $data
     * @param string $file
     * @return array
     */
    protected function loadCsv(array $data, string $file)
    {
        $columns = 0;
        $headers = $csv_fields = [];
        $fileHandle = fopen(storage_path("app/tmp_import/" . $file), 'r');
        $row_idx = 0;
        while (($row = fgetcsv($fileHandle)) !== FALSE) {
            $row_idx++;
            if (!$columns) {
                $columns = count($row);
            }

            if (!count($headers)) {
                $headers = $this->makeHeaders($row);
            }

            if ($row_idx == 1 && ($data['firstHeader'] ?? "")) {
                foreach ($row as $key => $val) {
                    $headers[$key]['name'] = $val;
                }
            }
            if ($row_idx == 3 && ($data['secondType'] ?? "")) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_type'] = ucfirst(strtolower($val));
                }
            }
            if ($row_idx == 4 && ($data['thirdSize'] ?? "")) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_size'] = (int)$val;
                }
            }
            if ($row_idx == 5 && ($data['fourthDefault'] ?? "")) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_default'] = $val;
                }
            }
            if ($row_idx == 6 && ($data['fifthRequired'] ?? "")) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_required'] = $val ? 1 : 0;
                }
            }

            if ($columns != count($row)) {
                return ['error' => "Incorrect csv format (your rows have different number of columns)!"];
            }

            //auto fill 'f_size' from columns
            foreach ($row as $key => $val) {
                if (strlen((string)$val) > $headers[$key]['f_size']) {
                    $headers[$key]['f_size'] = strlen((string)$val);
                }
            }
        }
        return $headers;
    }

    /**
     * @param array $data
     * @param string $file
     * @return array
     */
    protected function loadExcel(array $data, string $file)
    {
        $worksheet = ExcelWrapper::loadWorksheet($file, $data['xls_sheet'] ?? '');
        $first_row = ExcelWrapper::getWorkSheetRows($worksheet, true);

        return $this->makeHeaders(array_filter($first_row), !empty($data['firstHeader']));
    }

    /**
     * @param string $file_path
     * @return array
     */
    public function getXlsSheets(string $file_path)
    {
        try {
            return ExcelWrapper::getSheets($file_path);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get fields from remote MySQL table for import it as table
     *
     * @param array $mysql_settings
     * @return array
     */
    public function getFieldsFromMySQL(array $mysql_settings, $user_id)
    {
        //if need to save connection info
        $user_conn = $this->saveUserConnection($mysql_settings, $user_id);

        $setttings = collect($mysql_settings)->only(['host', 'login', 'pass'])->toArray();
        $setttings['db'] = 'information_schema';
        $this->service->configRemoteConnection($setttings);

        try {
            $columns = DB::connection('mysql_remote')->table('COLUMNS')
                ->where('TABLE_SCHEMA', '=', $mysql_settings['db'])
                ->where('TABLE_NAME', '=', $mysql_settings['table'])
                ->get();
        } catch (Exception $e) {
            return ['error' => true, 'msg' => 'Access to Information_schema denied!'];
        }

        if (!empty($columns)) {
            $headers = [];
            $idx = 0;
            foreach ($columns as $key => $col) {
                if (!in_array($col->COLUMN_NAME, ['id', 'createdBy', 'createdOn', 'modifiedBy', 'modifiedOn'])) {
                    $headers[$idx] = [
                        'status' => 'add',
                        'name' => $col->COLUMN_NAME,
                        'field' => '',
                        'col' => null,
                        'f_type' => ($col ? $col->DATA_TYPE : 'String'),
                        'f_size' => ($col->CHARACTER_MAXIMUM_LENGTH ? $col->CHARACTER_MAXIMUM_LENGTH : ($col->NUMERIC_PRECISION ? $col->NUMERIC_PRECISION : '')),
                        'f_default' => ($col->COLUMN_DEFAULT ? $col->COLUMN_DEFAULT : ''),
                        'f_required' => ($col->IS_NULLABLE != 'YES' ? 1 : 0)
                    ];
                    switch ($headers[$idx]['f_type']) {
                        case 'int':
                            $headers[$idx]['f_type'] = 'Integer';
                            break;
                        case 'decimal':
                            $headers[$idx]['f_type'] = 'Decimal';
                            break;
                        case 'datetime':
                            $headers[$idx]['f_type'] = 'Date Time';
                            break;
                        case 'date':
                            $headers[$idx]['f_type'] = 'Date';
                            break;
                        default:
                            $headers[$idx]['f_type'] = 'String';
                            break;
                    }
                    $idx++;
                }
            }
        } else {
            return ['error' => true, 'msg' => 'Columns not found!'];
        }

        return [
            'headers' => $headers,
            'mysql_fields' => array_pluck($headers, 'name'),
            'connection_id' => $user_conn->id
        ];
    }

    /**
     * Save User Connection if it isn`t present.
     *
     * @param array $data
     * @param $user_id
     * @return mixed
     */
    private function saveUserConnection(array $data, $user_id)
    {
        $conn_data = collect($data)->only(['name', 'host', 'login', 'pass', 'db', 'table', 'notes'])->toArray();
        $conn_data['user_id'] = $user_id;

        $exist = UserConnection::where('user_id', '=', $user_id)->where('name', '=', $data['name'])->first();
        $repo = new UserConnRepository();
        if ($exist) {
            $repo->updateUserConn($exist->id, $conn_data);
        } else {
            $exist = $repo->addUserConn($conn_data);
        }
        return $exist;
    }

    /**
     * Get Import status by id
     *
     * @param $import_ids
     * @return mixed
     */
    public function getImportStatus($import_ids)
    {
        return Import::whereIn('id', $import_ids)->get();
    }

    /**
     * Get Remote DataBases.
     *
     * @param $host
     * @param $login
     * @param $pass
     * @return mixed
     */
    public function getRemoteDBS($host, $login, $pass)
    {
        $this->service->configRemoteConnection([
            'host' => $host,
            'login' => $login,
            'pass' => $pass,
            'db' => 'information_schema'
        ]);

        $arr = DB::connection('mysql_remote')->select("SELECT schema_name FROM information_schema.schemata WHERE schema_name NOT IN ('information_schema', 'mysql', 'performance_schema')");

        $res = [];
        foreach ($arr as $obj) {
            $res[] = $obj->schema_name;
        }

        return $res;
    }

    /**
     * Get Remote Tables.
     *
     * @param $host
     * @param $login
     * @param $pass
     * @param $db
     * @return mixed
     */
    public function getRemoteTables($host, $login, $pass, $db)
    {
        $this->service->configRemoteConnection([
            'host' => $host,
            'login' => $login,
            'pass' => $pass,
            'db' => $db
        ]);

        $arr = DB::connection('mysql_remote')->select("SELECT table_name FROM information_schema.tables where table_schema='$db'");

        $res = [];
        foreach ($arr as $obj) {
            $res[] = $obj->table_name;
        }

        return $res;
    }

    /**
     * @param int $user_cloud_id
     * @param string $mime
     * @return array
     */
    public function allGoogleFiles(int $user_cloud_id, string $mime = '')
    {
        $token_json = (new UserCloudRepository())->getCloudToken($user_cloud_id);
        return (new GoogleApiModule())->driveFindFiles($token_json, $mime);
    }

    /**
     * @param int $user_cloud_id
     * @param string $spreadsheet_id
     * @return array
     */
    public function sheetsForGoogleTable(int $user_cloud_id, string $spreadsheet_id)
    {
        $token_json = (new UserCloudRepository())->getCloudToken($user_cloud_id);
        return (new GoogleApiModule())->getSheetsFromTable($token_json, $spreadsheet_id);
    }

    /**
     * @param int $user_cloud_id
     * @param string $file_id
     * @param string $path
     * @return string
     */
    public function storeGoogleFile(int $user_cloud_id, string $file_id, string $path)
    {
        $token_json = (new UserCloudRepository())->getCloudToken($user_cloud_id);
        return (new GoogleApiModule())->storeGoogleFile($token_json, $file_id, $path);
    }

    /**
     * @param int $user_cloud_id
     * @param array $extension
     * @return array
     */
    public function allDropboxFiles(int $user_cloud_id, array $extension)
    {
        return (new DropBoxApiModule())->driveFindFiles($user_cloud_id, $extension);
    }

    /**
     * @param int $user_cloud_id
     * @param string $file_id
     * @param string $path
     * @return string
     */
    public function storeDropboxFile(int $user_cloud_id, string $file_id, string $path)
    {
        return (new DropboxApiModule())->storeDropboxFile($user_cloud_id, $file_id, $path);
    }

    /**
     * @param int $user_cloud_id
     * @param string $extension
     * @return array
     */
    public function allOneDriveFiles(int $user_cloud_id, string $extension)
    {
        return (new OneDriveApiModule())->driveFindFiles($user_cloud_id, $extension);
    }

    /**
     * @param int $user_cloud_id
     * @param string $file_id
     * @param string $path
     * @return string
     */
    public function storeOneDriveFile(int $user_cloud_id, string $file_id, string $path)
    {
        return (new OneDriveApiModule())->storeOneDriveFile($user_cloud_id, $file_id, $path);
    }
}