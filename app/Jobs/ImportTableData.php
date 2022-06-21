<?php

namespace Vanguard\Jobs;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use SplFileObject;
use Vanguard\Classes\ExcelWrapper;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\Airtable\AirtableApi;
use Vanguard\Modules\Airtable\AirtableImporter;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableReferRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\HtmlXmlService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;
use function ini_set;

class ImportTableData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $table;
    protected $tableDataService;
    protected $user;
    protected $import_id;
    protected $htmlservice;
    protected $service;

    protected $pack_len = 100;

    /**
     * ImportTableData constructor.
     *
     * @param Table $table
     * @param array $data
     * @param User $user
     * @param $import_id
     */
    public function __construct(Table $table, array $data, User $user, $import_id)
    {
        $this->data = $data;
        $this->table = $table;
        $this->tableDataService = new TableDataService();
        $this->user = $user;
        $this->import_id = $import_id;
        $this->service = new HelperService();
        $this->htmlservice = new HtmlXmlService();

        //for setDefaults
        $this->table->load('_fields');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '256M');

        $is_admin = $this->user ? $this->user->isAdmin() : false;

        //CSV/Excel Import
        if ($this->data['import_type'] === 'csv' && !empty($this->data['csv_settings']['filename'])) {
            $this->CsvExcelImport();
        } //MYSQL IMPORT
        elseif ($this->data['import_type'] === 'mysql' && !empty($this->data['mysql_settings']['connection_id'])) {
            $this->mysqlImport();
        } //REFERENCE IMPORT
        elseif ($this->data['import_type'] === 'reference' && !empty($this->data['referenced_table'])) {
            $this->referenceImport();
        } //PASTE IMPORT
        elseif ($this->data['import_type'] === 'paste' && !empty($this->data['paste_file'])) {
            $this->pasteImport();
        } //GOOGLE SHEETS IMPORT
        elseif ($this->data['import_type'] === 'g_sheets' && !empty($this->data['g_sheets'])) {
            $this->googleSheetImport();
        } //WEB SCRAP IMPORT
        elseif ($this->data['import_type'] === 'web_scrap' && !empty($this->data['html_xml'])) {
            $this->webScrapImport();
        } //WEB SCRAP IMPORT
        elseif ($this->data['import_type'] === 'table_ocr' && !empty($this->data['ocr_data'])) {
            $this->ocrCsvImport();
        } //AIRTABLE IMPORT
        elseif ($this->data['import_type'] === 'airtable_import' && !empty($this->data['airtable_data'])) {
            $this->airtableImport();
        } //CHART EXPORT
        elseif ($this->data['import_type'] === 'chart_export' && !empty($this->data['chart_rows'])) {
            $this->chartExport();
        }

        //recalc all formulas if table has them
        $this->tableDataService->recalcTableFormulas($this->table);

        DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
            'status' => 'done'
        ]);
    }

    /**
     * @throws Exception
     */
    protected function CsvExcelImport()
    {
        $start = $end = 0;
        if (!empty($this->data['csv_settings']['firstHeader'])) {
            $start = 1;
        }
        if (!empty($this->data['csv_settings']['secondType'])) {
            $start = 2;
        }
        if (!empty($this->data['csv_settings']['thirdSize'])) {
            $start = 3;
        }
        if (!empty($this->data['csv_settings']['fourthDefault'])) {
            $start = 4;
        }
        if (!empty($this->data['csv_settings']['fifthRequired'])) {
            $start = 5;
        }
        if (!empty($this->data['csv_settings']['startRow'])) {
            $start = $this->data['csv_settings']['startRow'] > $start ? $this->data['csv_settings']['startRow'] : $start;
        }
        if (!empty($this->data['csv_settings']['endRow'])) {
            $end = $this->data['csv_settings']['endRow'] - 1;
        }

        $ext = $this->data['csv_settings']['extension']
            ?? strtolower(pathinfo($this->data['csv_settings']['filename'], PATHINFO_EXTENSION));
        if ($ext == 'csv') {
            $this->csvImport($start, $end, $this->data['csv_settings']['filename']);
        } elseif ($ext == 'xls' || $ext == 'xlsx') {
            $this->excelImport($start, $end);
        } else {
            Log::info('Csv/Excel Import - incorrect extension!');
            throw new Exception('Csv/Excel Import - incorrect extension!');
        }
    }

    /**
     * @param $start
     * @param $end
     */
    protected function csvImport($start, $end, $filename)
    {
        $fp = new SplFileObject(storage_path("app/tmp_import/" . $filename), 'r');
        $fp->seek(PHP_INT_MAX);
        $lines = $fp->key() + 1;
        $fp->rewind();

        //CSV Import
        for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

            DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
            ]);

            $pack = [];

            for ($i = 0; $i < $this->pack_len; $i++) {
                $data = $fp->fgetcsv();

                if ($fp->eof()) {
                    break;
                }

                if (($cur * $this->pack_len + $i) < $start || ($end && ($cur * $this->pack_len + $i) > $end)) {
                    continue;
                }

                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    if (!in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $present_val = trim($data[$col['col'] ?? null] ?? '');
                            $insert[$col['field']] = $this->dataConverter($data, $col, $present_val);
                        } catch (Exception $e) {
                            Log::info('ImportTableData - CSV Saving Error');
                            Log::info($e->getMessage());
                        }
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;
            }

            try {
                $this->tableDataService->insertMass($this->table, $pack);

                (new TableRepository())->onlyUpdateTable($this->table->id, [
                    'num_rows' => $this->table->num_rows + count($pack)
                ]);
            } catch (Exception $e) {
                Log::info('ImportTableData - CSV Import Error');
                Log::info($e->getMessage());
            }
        }

        unset($fp);
    }

    /**
     * Set Defaults for Insert.
     *
     * @param array $data_columns
     * @return array
     */
    protected function setDefaults(array $data_columns)
    {
        return $this->tableDataService->setDefaults($this->table, [], $this->user->id);
    }

    /**
     * Convert some datas to Special Formats.
     *
     * @param array $data
     * @param array $col
     * @param $val
     * @return array|string|string[]|null
     */
    protected function dataConverter(array $data, array $col, $val)
    {
        if (in_array($col['f_type'], ['Date', 'Date Time'])) {
            //prevent 0000-00-00 on importing
            if ($val === '') {
                $res = null;
            } else {
                $dtz = new DateTimeZone($this->user->timezone ?: 'UTC');
                try {
                    $res = new DateTime($val, $dtz);
                } catch (Exception $e) {
                }
                if (empty($res)) {
                    $res = DateTime::createFromFormat('d/m/Y', $val, $dtz);
                }
                if (empty($res)) {
                    $res = DateTime::createFromFormat('M j, Y', $val, $dtz);
                }

                $res = !empty($res)
                    ? $res->setTimezone(new DateTimeZone('UTC'))->format('Y/m/d H:i:s')
                    : null;
            }
        } elseif (in_array($col['f_type'], ['Currency', 'Integer', 'Decimal', 'Percentage'])) {
            if ($val === '') {
                $res = null;
            } else {
                $res = preg_replace('/[^\d\.-]/i', '', $val);
            }
        } else {
            $res = $val;
        }
        return $res;
    }

    /**
     * @param array $insert
     * @return array
     */
    protected function setCreatedAndModif(array $insert)
    {
        $insert['created_by'] = $this->user->id;
        $insert['created_on'] = now();
        $insert['modified_by'] = $this->user->id;
        $insert['modified_on'] = now();
        return $insert;
    }

    /**
     * @param $start
     * @param $end
     */
    protected function excelImport($start, $end)
    {
        $worksheet = ExcelWrapper::loadWorksheet($this->data['csv_settings']['filename'], $this->data['csv_settings']['xls_sheet'] ?? '');
        $collection = ExcelWrapper::getWorkSheetRows($worksheet);
        $lines = count($collection);

        //Excel Import
        for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

            DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
            ]);

            $pack = [];

            for ($i = 0; $i < $this->pack_len; $i++) {
                $ii = $cur * $this->pack_len + $i;

                $data = $collection[$ii] ?? [];
                //skip empty
                if (!$data) {
                    continue;
                }

                //skip not-needed
                if ($ii < $start || ($end && $ii > $end)) {
                    continue;
                }

                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    if (!in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $present_val = trim($data[$col['col'] ?? null] ?? '');
                            $insert[$col['field']] = $this->dataConverter($data, $col, $present_val);
                        } catch (Exception $e) {
                            Log::info('ImportTableData - Excel Saving Error');
                            Log::info($e->getMessage());
                        }
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;
            }

            try {
                $this->tableDataService->insertMass($this->table, $pack);

                (new TableRepository())->onlyUpdateTable($this->table->id, [
                    'num_rows' => $this->table->num_rows + count($pack)
                ]);
            } catch (Exception $e) {
                Log::info('ImportTableData - Excel Import Error');
                Log::info($e->getMessage());
            }
        }

    }

    /**
     * MySQL Data Import
     */
    protected function mysqlImport()
    {
        $this->service->configRemoteConnection($this->data['mysql_settings']);

        try {
            $lines = DB::connection('mysql_remote')->table($this->data['mysql_settings']['table'])->count();

            for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

                $all_rows = DB::connection('mysql_remote')
                    ->table($this->data['mysql_settings']['table'])
                    ->offset($cur * $this->pack_len)
                    ->limit($this->pack_len)
                    ->get();

                DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
                ]);

                $pack = [];

                foreach ($all_rows as $row) {
                    $row = (array)$row;
                    $insert = $this->setDefaults($this->data['columns']);

                    foreach ($this->data['columns'] as $col) {
                        if (!in_array($col['field'], $this->service->system_fields)) {
                            try {
                                $present_val = trim($row[$col['col'] ?? null] ?? '');
                                $insert[$col['field']] = $this->dataConverter($row, $col, $present_val);
                            } catch (Exception $e) {
                                Log::info('ImportTableData - MySQL Preparing Error');
                                Log::info($e->getMessage());
                            }
                        }
                    }

                    $insert = $this->setCreatedAndModif($insert);

                    $pack[] = $insert;
                }

                try {
                    $this->tableDataService->insertMass($this->table, $pack);

                    (new TableRepository())->onlyUpdateTable($this->table->id, [
                        'num_rows' => $this->table->num_rows + count($pack)
                    ]);
                } catch (Exception $e) {
                    Log::info('ImportTableData - MySQL Saving Error');
                    Log::info($e->getMessage());
                }
            }

        } catch (Exception $e) {
            Log::info('ImportTableData - MySql Data Import Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * Failed Job
     */
    public function failed()
    {
        DB::connection('mysql')
            ->table('imports')
            ->where('id', '=', $this->import_id)
            ->update([
                'status' => 'failed'
            ]);
    }

    /**
     * Referenced Data Import
     */
    protected function referenceImport()
    {
        $reference = (new TableReferRepository())->getRefer($this->data['referenced_table'], true);
        $ref_table = (new TableRepository())->getTable($reference->ref_table_id);

        try {
            $db_name = $this->table->db_name;

            //del old records
            DB::connection('mysql_data')->table($db_name)->where('refer_tb_id', '=', $ref_table->id)->delete();

            //get query
            $query = (new TableDataQuery($ref_table));
            if ($reference->ref_row_group_id) {
                $query->applySelectedRowGroup($reference->ref_row_group_id);
            }
            $query = $query->getQuery();

            //import rows from referenced table
            $lines = $query->count();
            for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

                $all_rows = $query->offset($cur * $this->pack_len)
                    ->limit($this->pack_len)
                    ->get()
                    ->toArray();

                DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
                ]);

                $pack = [];

                foreach ($all_rows as $row) {
                    $insert = $this->setDefaults($this->data['columns']);

                    foreach ($reference->_reference_corrs as $corrs) {
                        try {
                            if (!empty($row[$corrs->_ref_field->field])) {
                                $insert[$corrs->_field->field] = $row[$corrs->_ref_field->field];
                            }
                        } catch (Exception $e) {
                            Log::info('ImportTableData - Reference Preparing Error');
                            Log::info($e->getMessage());
                        }
                    }

                    $insert['refer_tb_id'] = $ref_table->id;
                    $insert = $this->setCreatedAndModif($insert);

                    $pack[] = $insert;
                }

                try {
                    $this->tableDataService->insertMass($this->table, $pack);

                    (new TableRepository())->onlyUpdateTable($this->table->id, [
                        'num_rows' => $this->table->num_rows + count($pack)
                    ]);
                } catch (Exception $e) {
                    Log::info('ImportTableData - Reference Saving Error');
                    Log::info($e->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::info('ImportTableData - Reference Data Import Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * Paste Data Import
     */
    protected function pasteImport()
    {
        $pasted_data = Storage::get('pasted/' . $this->data['paste_file']);

        $pack = [];
        $strings = preg_split('/\r\n|\r|\n/', $pasted_data);

        foreach ($strings as $row_idx => $row) {
            //skip first row as Headers.
            if ($row_idx == 0 && $this->data['paste_settings']['f_header']) {
                continue;
            }

            $row_cells = $this->service->pastedDataParser($row);

            $insert = $this->setDefaults($this->data['columns']);
            //fill only those data columns which numbers are setted in 'table columns settings'
            foreach ($this->data['columns'] as $col) {
                if (!in_array($col['field'], $this->service->system_fields)) {
                    try {
                        $present_val = trim($row_cells[$col['col'] ?? null] ?? '');
                        $insert[$col['field']] = $this->dataConverter($row_cells, $col, $present_val);
                    } catch (Exception $e) {
                        Log::info('ImportTableData - Paste Saving Error');
                        Log::info($e->getMessage());
                    }
                }
            }
            $insert = $this->setCreatedAndModif($insert);

            $repl_values = $this->data['paste_settings']['replace_values'];
            if (!empty($repl_values) && is_array($repl_values)) {
                $insert = array_merge($insert, $repl_values);
            }

            $pack[] = $insert;
        }

        try {
            $this->tableDataService->insertMass($this->table, $pack);

            (new TableRepository())->onlyUpdateTable($this->table->id, [
                'num_rows' => $this->table->num_rows + count($pack)
            ]);
        } catch (Exception $e) {
            Log::info('ImportTableData - Paste Data Import Error');
            Log::info($e->getMessage());
        }

    }

    /**
     * Google Sheets Data Import
     */
    protected function googleSheetImport()
    {
        try {
            $gsheet = $this->data['g_sheets'];
            $token_json = (new UserCloudRepository())->getCloudToken($gsheet['cloud_id']);
            $strings = $this->service->parseGoogleSheet($gsheet['sheet_id'], $gsheet['page'], $token_json);
            $lines = count($strings);

            $pack = [];
            foreach ($strings as $row_idx => $row) {
                //skip first row as Headers.
                if ($row_idx == 0 && $gsheet['settings']['f_header'] ?? null) {
                    continue;
                }

                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    if (!in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $present_val = trim($row[$col['col'] ?? null] ?? '');
                            $insert[$col['field']] = $this->dataConverter($row, $col, $present_val);
                        } catch (Exception $e) {
                            Log::info('ImportTableData - Google Sheets Preparing Error');
                            Log::info($e->getMessage());
                        }
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;

                if (count($pack) == $this->pack_len || $row_idx == ($lines - 1)) {
                    try {
                        $this->tableDataService->insertMass($this->table, $pack);

                        (new TableRepository())->onlyUpdateTable($this->table->id, [
                            'num_rows' => $this->table->num_rows + count($pack)
                        ]);
                    } catch (Exception $e) {
                        Log::info('ImportTableData - Google Sheets Saving Error');
                        Log::info($e->getMessage());
                    }
                    $pack = [];
                }

            }

        } catch (Exception $e) {
            Log::info('ImportTableData - Google Sheets Data Import Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * Web Scraping Data Import
     */
    protected function webScrapImport()
    {
        try {
            $htmlxml = $this->data['html_xml'];
            $strings = [];
            if ($htmlxml['web_action'] == 'html') {
                if (!empty($htmlxml['web_xpath'])) {
                    $strings = $this->htmlservice->parseXpathHtml($htmlxml['web_url'] ?? '', $htmlxml['web_xpath'] ?? '', true);
                } else {
                    $strings = $this->htmlservice->parsePageHtml($htmlxml['web_url'] ?? '', $htmlxml['web_query'] ?? '', $htmlxml['web_index'] ?? '', true);
                }
            } else {
                $strings = $this->htmlservice->parseXmlPage($htmlxml, true);
                $htmlxml['web_scrap_headers'] = 0; //don't skip first row.
            }
            $lines = count($strings);

            $pack = [];
            foreach ($strings as $row_idx => $row) {

                if ($row_idx == 0 && !empty($htmlxml['web_scrap_headers'])) {
                    continue;
                }

                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    if (!in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $present_val = trim($row[$col['col'] ?? null] ?? '');
                            $insert[$col['field']] = $this->dataConverter($row, $col, $present_val);
                        } catch (Exception $e) {
                            Log::info('ImportTableData - Web Scraping Preparing Error');
                            Log::info($e->getMessage());
                        }
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;

                if (count($pack) == $this->pack_len || $row_idx == ($lines - 1)) {
                    try {
                        $this->tableDataService->insertMass($this->table, $pack);

                        (new TableRepository())->onlyUpdateTable($this->table->id, [
                            'num_rows' => $this->table->num_rows + count($pack)
                        ]);
                    } catch (Exception $e) {
                        Log::info('ImportTableData - Web Scrap Saving Error');
                        Log::info($e->getMessage());
                    }
                    $pack = [];
                }

            }

        } catch (Exception $e) {
            Log::info('ImportTableData - Web Scrap Data Import Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     *
     */
    protected function ocrCsvImport()
    {
        $start = $end = 0;
        if (!empty($this->data['ocr_data']['first_header'])) {
            $start = 1;
        }
        $this->csvImport($start, $end, $this->data['ocr_data']['csv_source_file']);
    }

    /**
     * Airtable Data Import
     */
    protected function airtableImport()
    {
        try {
            $input = $this->data['airtable_data'];
            $key = (new UserConnRepository())->getUserApi($input['user_key_id'], true);
            $api = new AirtableApi($key->decryptedKey(), $key->air_base);
            $importer = new AirtableImporter();

            $air_columns = array_keys($api->tableFields($input['table_name']));
            $rows = $api->loadRows($input['table_name']);
            $this->storeAirtableRows($rows['records'], $air_columns, $importer);
            while ($rows['offset'] ?? '') {
                $rows = $api->loadRows($input['table_name'], $rows['offset']);
                $this->storeAirtableRows($rows['records'], $air_columns, $importer);
            }
            $importer->createDDLs($this->table);
        } catch (Exception $e) {
            Log::info('ImportTableData - Airtable Import Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * Airtable Data Import
     */
    protected function chartExport()
    {
        try {
            $pack = [];
            $maxlen = count($this->data['chart_rows']) - 1;
            foreach ($this->data['chart_rows'] as $row_idx => $row) {
                $insert = [];
                foreach ($this->data['columns'] as $col) {
                    $present_val = $row[$col['col'] ?? ''] ?? '';
                    if (trim($present_val) && $col['field'] && !in_array($col['field'], $this->service->system_fields)) {
                        $insert[$col['field']] = trim($present_val);
                    } else {
                        $insert[$col['field']] = '';
                    }
                }
                $insert = $this->setCreatedAndModif($insert);
                $pack[] = $insert;

                if (count($pack) == $this->pack_len || $row_idx == $maxlen) {
                    try {
                        $this->tableDataService->insertMass($this->table, $pack);
                        $pack = [];
                    } catch (Exception $e) {
                        Log::info('ImportTableData - Chart Export Insert Error');
                        Log::info($e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            Log::info('ImportTableData - Chart Export Error');
            Log::info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param array $rows
     * @param array $air_columns
     * @param AirtableImporter $importer
     */
    public function storeAirtableRows(array $rows, array $air_columns, AirtableImporter $importer)
    {
        $pack = 0;
        foreach ($rows as $row_idx => $row) {
            $insert = [];//$this->setDefaults($this->data['columns']);
            //fill only those data columns which numbers are setted in 'table columns settings'
            foreach ($this->data['columns'] as $col) {
                $f_key = $air_columns[$col['col'] ?? ''] ?? '';
                $present_val = $importer->parseAirtableVal($row['fields'][$f_key] ?? '', $col);

                if (trim($present_val) && $col['field'] && !in_array($col['field'], $this->service->system_fields)) {
                    try {
                        $insert[$col['field']] = trim($present_val);
                    } catch (Exception $e) {
                        Log::info('ImportTableData - Airtable Preparing Error');
                        Log::info($e->getMessage());
                    }
                }
            }
            $insert = $this->setCreatedAndModif($insert);

            try {
                $new_id = $this->tableDataService->insertRow($this->table, $insert, $this->table->user_id);
                $importer->moveAttachRowToArray($new_id);
                $pack++;
            } catch (Exception $e) {
                Log::info('ImportTableData - Airtable Insert Error');
                Log::info($e->getMessage());
            }
        }

        //DOWNLOAD AND SAVE ATTACHMENTS
        try {
            $importer->saveAttachmentsFiles($this->table);
        } catch (Exception $e) {
            Log::info('ImportTableData - Airtable File Parsing Error');
            Log::info($e->getMessage());
        }
    }
}
