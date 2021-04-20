<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\Import\CreateTableRequest;
use Vanguard\Http\Requests\Tablda\Import\DirectImportRequest;
use Vanguard\Http\Requests\Tablda\Import\ModifyTableRequest;
use Vanguard\Http\Requests\Tablda\Import\SendCSVRequest;
use Vanguard\Http\Requests\Tablda\Import\SendGSheetRequest;
use Vanguard\Http\Requests\Tablda\Import\SendMySQLRequest;
use Vanguard\Http\Requests\Tablda\Import\SendPasteRequest;
use Vanguard\Http\Requests\Tablda\Import\SendWebScrapRequest;
use Vanguard\Jobs\ImportTableData;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\ImportService;
use Vanguard\Services\Tablda\TableService;

class ImportController extends Controller
{
    private $tableService;
    private $importService;
    private $tableDataRepository;

    /**
     * ImportController constructor.
     *
     * @param tableService $tableService
     * @param ImportService $importService
     */
    public function __construct(TableService $tableService, ImportService $importService, TableDataRepository $tableDataRepository)
    {
        $this->tableService = $tableService;
        $this->importService = $importService;
        $this->tableDataRepository = $tableDataRepository;
    }

    /**
     * Create table with system fields
     *
     * @param CreateTableRequest $request
     * @return array
     */
    public function createTable(CreateTableRequest $request) {
        $user = auth()->user();
        $is_admin = auth()->user() ? auth()->user()->isAdmin() : false;
        $available_tables = (int)$user->_available_features->q_tables;
        if ($is_admin || !$available_tables || ($user->_tables()->count() < $available_tables)) {

            $data = $this->importService->createTable(
                $request->only([
                    'name','rows_per_page','autoload_new_data','pub_hidden','initial_view_id',
                    'add_map','add_bi','add_request','add_alert','add_kanban','add_gantt','add_email','add_calendar',
                    'app_font_size','app_font_color','navbar_bg_color','button_bg_color','ribbon_bg_color',
                    'main_bg_color','table_hdr_bg_color','is_public'
                ]),
                auth()->id(),
                $request->folder_id,
                $request->path
            );
            return response($data, empty($data['error']) ? 200 : 500);

        } else {
            return response('Forbidden', 403);
        }
    }

    /**
     * Modify columns for user`s table
     *
     * @param ModifyTableRequest $request
     * @return array
     */
    public function modifyTable(ModifyTableRequest $request) {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('modifyTable', [TableData::class, $table]);

        $data = $this->importService->modifyTable($table, $request->all());

        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Delete user`s table
     *
     * @param GetTableRequest $request
     * @return array
     */
    public function deleteTable(GetTableRequest $request) {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $data = $this->importService->deleteTable($table);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from CSV.
     *
     * @param SendCSVRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsFromCSV(SendCSVRequest $request) {
        $request->csv_settings = json_decode($request->csv_settings, true);
        $data = $this->importService->getFieldsFromCSV($request->file('csv'), $request->csv_link, $request->csv_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Paste Import'.
     *
     * @param SendPasteRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsFromPaste(SendPasteRequest $request) {
        $data = $this->importService->getFieldsFromPaste($request->paste_data, $request->paste_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Paste Import'.
     *
     * @param SendGSheetRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsFromGSheet(SendGSheetRequest $request) {
        $data = $this->importService->getFieldsFromGSheet($request->g_sheet_link, $request->g_sheet_name, $request->g_sheet_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Web Scraping'.
     *
     * @param SendWebScrapRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getScrapWeb(SendWebScrapRequest $request) {
        $data = '';
        if ($request->web_action == 'preload') {
            $data = $this->importService->preloadHtmlXml($request->web_url);
        }
        if ($request->web_action == 'html') {
            $data = $this->importService->getFieldsFromHtml($request->web_url, $request->web_xpath, $request->web_query, $request->web_index);
        }
        if ($request->web_action == 'xml') {
            $data = $this->importService->getFieldsFromXml($request->web_url, $request->web_xpath);
        }
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from remote MySQL.
     *
     * @param SendMySQLRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsFromMySQL(SendMySQLRequest $request) {
        $data = $this->importService->getFieldsFromMySQL($request->mysql_settings, auth()->id());
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get Import Status
     *
     * @param Request $request
     * @return mixed
     */
    public function getImportStatus(Request $request) {
        return $this->importService->getImportStatus($request->import_id);
    }

    /**
     * Get Remote DataBases
     *
     * @param Request $request
     * @return mixed
     */
    public function getRemoteDBS(Request $request) {
        return $this->importService->getRemoteDBS($request->host, $request->login, $request->pass);
    }

    /**
     * Get Remote Tables
     *
     * @param Request $request
     * @return mixed
     */
    public function getRemoteTables(Request $request) {
        return $this->importService->getRemoteTables($request->host, $request->login, $request->pass, $request->db);
    }

    /**
     * directImportData
     *
     * @param DirectImportRequest $request
     * @return mixed
     */
    public function directImportData(DirectImportRequest $request) {
        if (auth()->user()) {
            $table = $this->tableService->getTable($request->table_id);
            $this->authorize('isOwner', [TableData::class, $table]);
            (new ImportTableData($table, $request->all(), auth()->user(), 0))->handle();
            return ['success' => 1];
        } else {
            return ['error' => 1];
        }
    }
}
