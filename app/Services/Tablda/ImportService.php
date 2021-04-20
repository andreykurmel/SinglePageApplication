<?php


namespace Vanguard\Services\Tablda;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Jobs\ImportTableData;
use Vanguard\Jobs\RecalcTableFormula;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableReference;
use Vanguard\Models\User\UserConnection;
use Vanguard\Repositories\Tablda\CopyTableRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
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
     * @param $jstree_folder
     * @param $new_user_id
     * @param $parent_folder_id
     * @param string $new_name
     * @param bool $overwrite
     * @return array
     */
    public function copyFolderAndSubfolder($jstree_folder, $new_user_id, $parent_folder_id, $new_name = '', $overwrite = false)
    {
        $fld_name = $new_name ?: $jstree_folder['init_name'] ?? ($jstree_folder['text'] ?? '');
        if ($tested = $this->folderRepository->testNameOnLvl($fld_name, $parent_folder_id, $new_user_id)) {
            if (!$overwrite) {
                return ['error' => true, 'msg' => 'Already Copied.', 'already_copied' => true];
            } else {
                (new FolderService())->deleteFolderWithSubs($tested->id);
            }
        }

        //copy folders and tables
        $new_fld_id = $this->copyFolderTreeForOthers($jstree_folder, $new_user_id, $parent_folder_id);
        //copy table settings
        $this->copyFolderTreeForOthers($jstree_folder, $new_user_id, $parent_folder_id, 1);

        //new menutree user hash
        (new UserRepository())->newMenutreeHash($new_user_id);

        return ['new_folder_id' => $new_fld_id];
    }

    /**
     * Copy Folder with Sub-Folders and Tables to another User.
     *
     * @param $jstree_folder
     * @param $new_user_id
     * @param $parent_folder_id
     * @param $copy_tb_settings_mode -- function runned second time to copy Settings for Copied Tables and Folders.
     * @return int
     */
    private function copyFolderTreeForOthers(array &$jstree_folder, $new_user_id, $parent_folder_id, $copy_tb_settings_mode = false)
    {
        $created_fld_id = null;
        if ($this->jsonObjectIsSelected($jstree_folder)) {

            $created_fld_id = $this->getOrCopyFolder($jstree_folder, $parent_folder_id, $new_user_id);

            if ($created_fld_id) {

                foreach ($jstree_folder['children'] as &$inner) {
                    $type = $inner['li_attr']['data-type'] ?? '';
                    if ($type == 'table' && $this->jsonObjectIsSelected($inner)) {

                        $table = $this->tableRepository->getTable($inner['li_attr']['data-object']['id'] ?? 0);
                        $tb_settings = !empty($inner['li_attr']['data-copy-settings']) ? $inner['li_attr']['data-copy-settings'] : null;

                        //instead of '$copy_tb_settings_mode'
                        if (empty($inner['li_attr']['data-new-table'])) {
                            $res = $this->copyTable($table, $new_user_id, $created_fld_id, $tb_settings);
                            $inner['li_attr']['data-new-table'] = $res['new_table'];
                            $this->copyTableRepository->copied_table_compares[$table->id] = $res['new_table']->id;
                        } else {
                            $this->copyTableSettings($table, $new_user_id, $inner['li_attr']['data-new-table'], $tb_settings);
                        }

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
            $old['structure'],
            [
                'in_shared' => $old['in_shared']
            ]
        );

        //copy Folder Icon.
        if ($old['icon_path']) {
            $new_icon_path = preg_replace('/\/\d+_/i', '/' . $folder->id . '_', $old['icon_path']);
            $this->folderRepository->updateFolder($folder->id, ['icon_path' => $new_icon_path]);
            //copy folder icon in Storage
            $old_path = storage_path('app/public/') . $old['icon_path'];
            if (\File::exists($old_path)) {
                $new_path = storage_path('app/public/') . $new_icon_path;
                \File::copy($old_path, $new_path);
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
     * @return array
     */
    public function copyTable(Table $table, $new_user_id, int $new_folder_id, $settings = null, $overwrite = false)
    {
        $data = $this->prepareData($table->only(['name', 'rows_per_page', 'notes']), $new_user_id);

        if ($tested = $this->tableRepository->testNameOnLvl($data['name'], $new_folder_id, $new_user_id)) {
            if (!$overwrite) {
                return ['error' => true, 'msg' => 'Already Copied.', 'already_copied' => true];
            } else {
                $this->deleteTable($tested);
            }
        }

        //config connection
        $db = $this->service->getConnectionForTable($table);

        //copy in DB_DATA
        if (is_null($settings) || !empty($settings['data'])) {
            //with data
            $db->select('CREATE TABLE `' . $data['db_name'] . '` SELECT * FROM `' . $table->db_name . '`');
        } else {
            //only structure
            $db->select('CREATE TABLE `' . $data['db_name'] . '` LIKE `' . $table->db_name . '`');
        }
        //create 'id' as primary key
//        $db->select('ALTER TABLE `' . $data['db_name'] . '` DROP COLUMN `id`');
        $db->select('UPDATE `' . $data['db_name'] . '` SET `created_on` = now(), `modified_on` = now()');
        $db->select('ALTER TABLE `' . $data['db_name'] . '` CHANGE `id` `id` INT(10) AUTO_INCREMENT, add PRIMARY KEY (`id`)');

        //copy in DB_SYS
        $initial_columns = $this->getInitialColumns($table);
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
    public function prepareData(Array $data, $user_id)
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
     * Get Initial Columns From Table to Add them to another Table.
     *
     * @param Table $table
     * @return mixed
     */
    public function getInitialColumns(Table $table)
    {
        $table->load([
            '_fields' => function ($f) {
                $f->joinOwnerHeader();
            }
        ]);

        $init_fields = [];
        foreach ($table->_fields as $fld) {
            $init_fields[] = $fld->only((new TableField())->getFillable());
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
    public function createTable(Array $data, $user_id, $folder_id, $folder_path = '', $initial_columns = [])
    {
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
            ['field' => 'id', 'name' => 'ID', 'f_type' => 'Auto Number', 'is_showed' => 0],
            ['field' => 'created_by', 'name' => 'Created By', 'f_type' => 'User', 'is_showed' => 0],
            ['field' => 'created_on', 'name' => 'Created On', 'f_type' => 'Date Time', 'is_showed' => 0],
            ['field' => 'modified_by', 'name' => 'Modified By', 'f_type' => 'User', 'is_showed' => 0],
            ['field' => 'modified_on', 'name' => 'Modified On', 'f_type' => 'Date Time', 'is_showed' => 0],
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
     * @throws \Exception
     */
    public function copyTableSettings(Table $table, $new_user_id, Table $new_table, $settings = null)
    {
        $errors = [];
        $new_table->update($table->getCopyAttrs());

        try {
            $this->copyTableRepository->copyHeaderSettings($table, $new_table);
        } catch (\Exception $e) {
            $errors[] = 'Headers Settings';
        }

        //NOTE: shouldn't change order

        if (is_null($settings) || !empty($settings['grouping_rcs'])) {
            $new_user = User::where('id', $new_user_id)->first();
            try {
                $this->copyTableRepository->copyReferenceConditions($table, $new_table, $new_user);
            } catch (\Exception $e) {
                $errors[] = 'Reference Conditions';
            }
        }

        if (is_null($settings) || !empty($settings['grouping_rows'])) {
            try {
                $this->copyTableRepository->copyRowGroups($table, $new_table);
            } catch (\Exception $e) {
                $errors[] = 'Row Groups';
            }
        }
        if (is_null($settings) || !empty($settings['grouping_columns'])) {
            try {
                $this->copyTableRepository->copyColumnGroups($table, $new_table);
            } catch (\Exception $e) {
                $errors[] = 'Column Groups';
            }
        }

        if (is_null($settings) || !empty($settings['ddls'])) {
            try {
                $this->copyTableRepository->copyDDLs($table, $new_table);
            } catch (\Exception $e) {
                $errors[] = 'DDLs';
            }
        }
        if (is_null($settings) || !empty($settings['basics'])) {
            try {
                $this->copyTableRepository->copyBasics($table, $new_table);
            } catch (\Exception $e) {
                $errors[] = 'Basics';
            }
        }
        if (is_null($settings) || !empty($settings['links'])) {
            try {
                $this->copyTableRepository->copyLinks($table, $new_table);
            } catch (\Exception $e) {
                $errors[] = 'Links';
            }
        }
        if (is_null($settings) || !empty($settings['cond_formats'])) {
            try {
                $this->copyTableRepository->copyCondFormats($table, $new_table, $new_user_id);
            } catch (\Exception $e) {
                $errors[] = 'Cond Formats';
            }
        }

        try {
            $this->copyTableRepository->copyMapIconsAndCharts($table, $new_table);
        } catch (\Exception $e) {
            $errors[] = 'Map Icons And Charts';
        }

        if ($errors) {
            throw new \Exception(implode(', ',$errors).' are not copied!', 1);
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
     * @return array|mixed
     */
    public function saveCopyTable(Table $table, $new_user_id, $settings = null, $overwrite = false)
    {
        $sys_folder = $this->folderRepository->getSysFolder($new_user_id, 'TRANSFERRED');

        $res = $this->copyTable($table, $new_user_id, $sys_folder->id, $settings, $overwrite);
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
    public function modifyTable(Table $table, Array $data)
    {
        //if needs to replace table with columns -> prepare to do it.
        if (in_array($data['import_type'], ['csv', 'mysql', 'remote', 'paste', 'g_sheet']) && $data['import_action'] === 'New') {
            $table = $this->prepareTableForReplace($table, $data);
            if (!empty($table['error'])) {
                return $table;
            }
        }

        $idx = 0;
        foreach ($data['columns'] as $col) {
            if ($col['status'] != 'add') $idx++;
        }
        //prepare 'columns' and build db_name for columns from provided column names.
        foreach ($data['columns'] as &$col) {
            $col['name'] = $col['name'] ?: 'col_' . $idx;
            $col['field'] = empty($col['field']) ? $this->getDbField($col['name'], ++$idx) : $col['field'];
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

        //modify table references for that import type.
        if ($data['import_type'] === 'reference') {
            $res = $this->updateTableReferences($table, $data['referenced_table']);
            if ($res['error']) {
                return $res;
            }
        }

        //import data
        if (in_array($data['import_type'], ['csv', 'mysql', 'reference', 'paste', 'g_sheet', 'web_scrap'])) {
            $job = Import::create([
                'file' => "app/csv/" . $data['csv_settings']['filename'],
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
        $this->tableRepository->onlyUpdateTable($table->id, [
            'source' => $data['import_type'],
            'connection_id' => in_array($data['import_type'], ['mysql', 'remote']) ? $data['mysql_settings']['connection_id'] : null,
            'num_columns' => count($this->fieldRepository->getFieldsForTable($table->id)),
        ]);

        return ['error' => false, 'msg' => '', 'job_id' => isset($job) ? $job->id : 0];
    }

    /**
     * Delete table and create again for Replacing
     *
     * @param \Vanguard\Models\Table\Table $table
     * @param array $data
     * @return array|bool|null
     */
    public function prepareTableForReplace(Table $table, Array $data)
    {
        $folder = $table->_folder_links()->where('type', '=', 'table')->first();
        $folder_id = $folder->folder_id;
        $user_id = $table->user_id;
        $tb = $table->only(['db_name', 'name', 'rows_per_page', 'notes']);

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
     * Delete user`s table
     *
     * @param \Vanguard\Models\Table\Table $table
     * @return array|bool|null
     */
    public function deleteTable(Table $table)
    {
        $this->fileRepository->deleteAttachments($table);
        $error = $this->importRepository->deleteTable($table->db_name);
        if ($error) {
            $res = ['error' => true, 'msg' => $error];
        }
        $deleted = $this->tableRepository->deleteTable($table->id);
        if (!$deleted) {
            $res = ['error' => true];
        }
        return (isset($res) ? $res : ['error' => false]);
    }

    /**
     * Get db_name for field from name
     *
     * @param $name
     * @param $idx
     * @return string
     */
    private function getDbField($name, $idx)
    {
        $name = strtolower($name);
        if (preg_match('/[^a-zA-Z]/i', $name)) {
            $name = 'f' . substr($name, 1);
        }
        $name = preg_replace('/ /i', '_', substr($name, 0, 50));
        return preg_replace('/[^\w\d_]/i', '', $name) . '_' . ($idx);
    }

    /**
     * Update table reference records for table.
     *
     * @param \Vanguard\Models\Table\Table $table
     * @param array $references
     * @return array
     */
    private function updateTableReferences(Table $table, Array $references)
    {
        $response = ['error' => false, 'msg' => ''];

        TableReference::where('table_id', '=', $table->id)->where('ref_table_id', '=', $references['id'])->delete();
        if (!$references['only_del']) {
            foreach ($references['objects'] as $ref) {
                if (!empty($ref['ref_field_id'])) {
                    try {
                        $ref = collect($ref)->only(['table_id', 'table_field_id', 'ref_table_id', 'ref_field_id'])->toArray();
                        TableReference::create(array_merge($ref, $this->service->getModified(), $this->service->getCreated()));
                    } catch (\Exception $e) {
                        $response = ['error' => true, 'msg' => 'Table references inserting down!' . $e->getMessage()];
                    }
                }
            }
        }

        return $response;
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
     * Get fields from Google Sheet for import it as table
     *
     * @param $g_sheet_link
     * @param $g_sheet_name
     * @param $settings
     * @return array
     */
    public function getFieldsFromGSheet(string $g_sheet_link, string $g_sheet_name, array $settings)
    {
        try {
            $user = auth()->user();
            $token_json = $user
                ? $user->_clouds()->where('cloud', 'Google')->whereNotNull('token_json')->first()
                : null;
            $token_json = $token_json ? $token_json->gettoken() : null;
            $strings = $this->service->parseGoogleSheet($g_sheet_link, $g_sheet_name, $token_json);
        } catch (\Exception $e) {
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
     * @param $query
     * @param $index
     * @return array
     */
    public function getFieldsFromHtml($url, $xpath, $query, $index)
    {
        if ($xpath) {
            $row = $this->htmlservice->parseXpathHtml($url, $xpath);
        } else {
            $row = $this->htmlservice->parsePageHtml($url, $query, $index);
        }
        $headers = $this->makeHeaders($row);
        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
    }

    /**
     * @param $url
     * @param $xpath
     * @return array
     */
    public function getFieldsFromXml($url, $xpath)
    {
        $row = $this->htmlservice->parseXmlPage($url, $xpath);
        $headers = $this->makeHeaders($row);
        return [
            'headers' => $headers,
            'fields' => array_pluck($headers, 'name'),
        ];
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
     * @param array $row
     * @param bool $use_val
     * @return array
     */
    protected function makeHeaders(array $row, bool $use_val = false)
    {
        $headers = [];
        foreach ($row as $key => $val) {
            $headers[$key]['name'] = $use_val ? ($val ?: 'col_'.$key) : 'col_'.$key;
            $headers[$key]['status'] = 'add';
            $headers[$key]['field'] = '';
            $headers[$key]['col'] = null;
            $headers[$key]['f_type'] = 'String';
            $headers[$key]['f_size'] = 64;
            $headers[$key]['f_default'] = '';
            $headers[$key]['f_required'] = 0;
        }
        return $headers;
    }

    /**
     * Get fields from CSV file for import it as table
     *
     * @param $upload_file
     * @param $file_link
     * @param array $data
     * @return array
     */
    public function getFieldsFromCSV($upload_file, $file_link, Array $data)
    {
        if ($file_link) {
            $tmp_csv = time() . "_" . rand() . ".csv";
            if (!Storage::put("csv/" . $tmp_csv, file_get_contents($file_link))) {
                return ['error' => "File accessing error!"];
            }
        } else {
            $tmp_csv = time() . "_" . rand() . ".csv";
            $upload_file->storeAs('csv', $tmp_csv);
        }

        $columns = 0;
        $headers = $csv_fields = [];
        $fileHandle = fopen(storage_path("app/csv/" . $tmp_csv), 'r');
        $row_idx = 0;
        while (($row = fgetcsv($fileHandle)) !== FALSE) {
            $row_idx++;
            if (!$columns) {
                $columns = count($row);
            }

            if (!count($headers)) {
                $headers = $this->makeHeaders($row);
            }

            if ($row_idx == 1 && $data['firstHeader']) {
                foreach ($row as $key => $val) {
                    $headers[$key]['name'] = $val;
                }
            }
            if ($row_idx == 3 && $data['secondType']) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_type'] = ucfirst(strtolower($val));
                }
            }
            if ($row_idx == 4 && $data['thirdSize']) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_size'] = (int)$val;
                }
            }
            if ($row_idx == 5 && $data['fourthDefault']) {
                foreach ($row as $key => $val) {
                    $headers[$key]['f_default'] = $val;
                }
            }
            if ($row_idx == 6 && $data['fifthRequired']) {
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

        return [
            'headers' => $headers,
            'csv_fields' => array_pluck($headers, 'name'),
            'csv_file' => $tmp_csv
        ];
    }

    /**
     * Get fields from remote MySQL table for import it as table
     *
     * @param array $mysql_settings
     * @return array
     */
    public function getFieldsFromMySQL(Array $mysql_settings, $user_id)
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
        } catch (\Exception $e) {
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
    private function saveUserConnection(Array $data, $user_id)
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
     * @param $import_id
     * @return mixed
     */
    public function getImportStatus($import_id)
    {
        return Import::where('id', '=', $import_id)->first();
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
}