<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\Import\AllGoogleSpreadsheetRequest;
use Vanguard\Http\Requests\Tablda\Import\CreateTableRequest;
use Vanguard\Http\Requests\Tablda\Import\DirectImportRequest;
use Vanguard\Http\Requests\Tablda\Import\DropboxFileRequest;
use Vanguard\Http\Requests\Tablda\Import\ModifyTableRequest;
use Vanguard\Http\Requests\Tablda\Import\OcrCheckKeyRequest;
use Vanguard\Http\Requests\Tablda\Import\OcrParseImageRequest;
use Vanguard\Http\Requests\Tablda\Import\OneDriveFileRequest;
use Vanguard\Http\Requests\Tablda\Import\SendAirtableRequest;
use Vanguard\Http\Requests\Tablda\Import\SendCSVRequest;
use Vanguard\Http\Requests\Tablda\Import\SendGSheetRequest;
use Vanguard\Http\Requests\Tablda\Import\SendMySQLRequest;
use Vanguard\Http\Requests\Tablda\Import\SendPasteRequest;
use Vanguard\Http\Requests\Tablda\Import\SendWebScrapRequest;
use Vanguard\Http\Requests\Tablda\Import\SpreadsheetFileRequest;
use Vanguard\Jobs\ImportTableData;
use Vanguard\Models\Table\TableData;
use Vanguard\Modules\Ocr\ExtracttableOcr;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\ImportService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\FileHelper;

class ImportController extends Controller
{
    private $tableService;
    private $importService;

    /**
     * ImportController constructor.
     *
     * @param tableService $tableService
     * @param ImportService $importService
     */
    public function __construct(TableService $tableService, ImportService $importService)
    {
        $this->tableService = $tableService;
        $this->importService = $importService;
    }

    /**
     * Create table with system fields
     *
     * @param CreateTableRequest $request
     * @return array
     */
    public function createTable(CreateTableRequest $request)
    {
        $user = auth()->user();
        $is_admin = auth()->user() ? auth()->user()->isAdmin() : false;
        $available_tables = (int)$user->_available_features->q_tables;
        if ($is_admin || !$available_tables || ($user->_tables()->count() < $available_tables)) {

            $data = $this->importService->createTable(
                $request->only([
                    'name', 'rows_per_page', 'autoload_new_data', 'pub_hidden', 'initial_view_id', 'add_tournament','add_report', 'primary_align',
                    'add_map', 'add_bi', 'add_request', 'add_alert', 'add_kanban', 'add_gantt', 'add_email', 'add_calendar', 'add_twilio',
                    'app_font_size', 'app_font_color', 'app_font_family', 'appsys_font_size', 'appsys_font_color', 'appsys_font_family',
                    'appsys_tables_font_size', 'appsys_tables_font_color', 'appsys_tables_font_family', 'filters_on_top', 'filters_ontop_pos',
                    'navbar_bg_color', 'button_bg_color', 'ribbon_bg_color', 'main_bg_color', 'table_hdr_bg_color', 'is_public', 'enabled_activities',
                    'primary_view', 'primary_width', 'listing_fld_id', 'listing_rowswi', 'add_ai','add_grouping','add_simplemap',
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
    public function modifyTable(ModifyTableRequest $request)
    {
        if ($request->new_table) {
            $res = $this->importService->createTable($request->new_table, auth()->id(), $request->new_table['folder_id']);
            $table = $this->tableService->getTable($res['table_id']);
        } else {
            $table = $this->tableService->getTable($request->table_id);
            $this->authorize('modifyTable', [TableData::class, $table]);
        }

        $data = $this->importService->modifyTable($table, $request->all());

        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function presaveColumn(Request $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('modifyTable', [TableData::class, $table]);
        return (new TableFieldRepository())->addFieldsForCreatedTable($table->id, $request->pre_columns, true);
    }

    /**
     * Delete user`s table
     *
     * @param GetTableRequest $request
     * @return array
     */
    public function deleteTable(GetTableRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);

        $this->authorize('isOwner', [TableData::class, $table]);

        $data = $this->importService->deleteTable($table);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from CSV.
     *
     * @param SendCSVRequest $request
     * @return ResponseFactory|Response
     */
    public function getFieldsFromCSV(SendCSVRequest $request)
    {
        $request->csv_settings = json_decode($request->csv_settings, true);
        $data = $this->importService->getFieldsFromCSV($request->file('csv'), $request->csv_link, $request->csv_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getXlsSheets(Request $request)
    {
        return $this->importService->getXlsSheets($request->file_path);
    }

    /**
     * Get columns with info for further import table from 'Paste Import'.
     *
     * @param SendPasteRequest $request
     * @return ResponseFactory|Response
     */
    public function getFieldsFromPaste(SendPasteRequest $request)
    {
        $data = $this->importService->getFieldsFromPaste($request->paste_data, $request->paste_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Paste Import'.
     *
     * @param SendGSheetRequest $request
     * @return ResponseFactory|Response
     */
    public function getFieldsFromGSheet(SendGSheetRequest $request)
    {
        $data = $this->importService->getFieldsFromGSheet($request->g_sheets_file, $request->g_sheets_element, $request->g_sheets_settings);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Web Scraping'.
     *
     * @param SendWebScrapRequest $request
     * @return ResponseFactory|Response
     */
    public function getScrapWeb(SendWebScrapRequest $request)
    {
        $data = ['no_action' => true];
        if ($request->web_action == 'preload') {
            $data = $this->importService->preloadHtmlXml($request->web_url);
        }
        if ($request->web_action == 'html') {
            $data = $this->importService->getFieldsFromHtml($request->web_url, $request->web_xpath, $request->web_query, $request->web_index, $request->web_scrap_headers);
        }
        if ($request->web_action == 'xml') {
            if (!$request->web_xpath) {
                throw new Exception('XPath is empty!', 1);
            }
            $data = $this->importService->getFieldsFromXml($request->all());
        }
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Airtable'.
     *
     * @param SendAirtableRequest $request
     * @return ResponseFactory|Response
     */
    public function getAirtable(SendAirtableRequest $request)
    {
        $key = (new UserConnRepository())->getUserApi($request->user_key_id);
        if (!$key) {
            throw new Exception('Incorrect Api Key', 1);
        }
        $data = $this->importService->getFieldsFromAirtable($key, $request->table_name, $request->fromtype);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from 'Airtable'.
     *
     * @param SendAirtableRequest $request
     * @return ResponseFactory|Response
     */
    public function getAirtableColValues(SendAirtableRequest $request)
    {
        $key = (new UserConnRepository())->getUserApi($request->user_key_id);
        if (!$key) {
            throw new Exception('Incorrect Api Key', 1);
        }
        $data = $this->importService->getColValuesFromAirtable($key, $request->table_name, $request->col_name, $request->fromtype);
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get columns with info for further import table from remote MySQL.
     *
     * @param SendMySQLRequest $request
     * @return ResponseFactory|Response
     */
    public function getFieldsFromMySQL(SendMySQLRequest $request)
    {
        $data = $this->importService->getFieldsFromMySQL($request->mysql_settings, auth()->id());
        return response($data, empty($data['error']) ? 200 : 500);
    }

    /**
     * Get Import Status
     *
     * @param Request $request
     * @return mixed
     */
    public function getImportStatus(Request $request)
    {
        return $this->importService->getImportStatus($request->import_jobs);
    }

    /**
     * Get Remote DataBases
     *
     * @param Request $request
     * @return mixed
     */
    public function getRemoteDBS(Request $request)
    {
        return $this->importService->getRemoteDBS($request->host, $request->login, $request->pass);
    }

    /**
     * Get Remote Tables
     *
     * @param Request $request
     * @return mixed
     */
    public function getRemoteTables(Request $request)
    {
        return $this->importService->getRemoteTables($request->host, $request->login, $request->pass, $request->db);
    }

    /**
     * directImportData
     *
     * @param DirectImportRequest $request
     * @return mixed
     */
    public function directImportData(DirectImportRequest $request)
    {
        if (auth()->user()) {
            $table = $this->tableService->getTable($request->table_id);
            $this->authorizeForUser(auth()->user(), 'insert', [TableData::class, $table, $request->all()]);
            dispatch(new ImportTableData($table, $request->all(), auth()->user(), 0));
            return ['success' => 1];
        } else {
            return ['error' => 1];
        }
    }

    /**
     * @param AllGoogleSpreadsheetRequest $request
     * @return array
     */
    public function allGoogleFiles(AllGoogleSpreadsheetRequest $request)
    {
        return $this->importService->allGoogleFiles($request->cloud_id, $request->mime);
    }

    /**
     * @param SpreadsheetFileRequest $request
     * @return array
     */
    public function sheetsForGoogleTable(SpreadsheetFileRequest $request)
    {
        return $this->importService->sheetsForGoogleTable($request->cloud_id, $request->spreadsheet_id);
    }

    /**
     * @param SpreadsheetFileRequest $request
     * @return string
     */
    public function storeGoogleFile(SpreadsheetFileRequest $request)
    {
        return $this->importService->storeTmpGoogleFile($request->cloud_id, $request->file_id, $request->new_path);
    }

    /**
     * @param DropboxFileRequest $request
     * @return array
     */
    public function allDropboxFiles(DropboxFileRequest $request)
    {
        return $this->importService->allDropboxFiles($request->cloud_id, $request->extensions);
    }

    /**
     * @param DropboxFileRequest $request
     * @return string
     */
    public function storeDropboxFile(DropboxFileRequest $request)
    {
        return $this->importService->storeTmpDropboxFile($request->cloud_id, $request->file_id, $request->new_path);
    }

    /**
     * @param OneDriveFileRequest $request
     * @return array
     */
    public function allOneDriveFiles(OneDriveFileRequest $request)
    {
        return $this->importService->allOneDriveFiles($request->cloud_id, $request->extension);
    }

    /**
     * @param OneDriveFileRequest $request
     * @return string
     */
    public function storeOneDriveFile(OneDriveFileRequest $request)
    {
        return $this->importService->storeTmpOneDriveFile($request->cloud_id, $request->file_id, $request->new_path);
    }

    /**
     * @param OcrCheckKeyRequest $request
     * @return array
     */
    public function ocrCheckKey(OcrCheckKeyRequest $request)
    {
        return (new ExtracttableOcr($request->key))->validateKey();
    }

    /**
     * @param OcrParseImageRequest $request
     * @return string[]
     * @throws Exception
     */
    public function ocrParseImage(OcrParseImageRequest $request)
    {
        $api = new ExtracttableOcr($request->key);
        $full_path = FileHelper::tmpImportFolder($request->file_name);
        return $api->sendFileToOcr($full_path);
    }
}
