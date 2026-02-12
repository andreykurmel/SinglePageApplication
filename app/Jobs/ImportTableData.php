<?php

namespace Vanguard\Jobs;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use SplFileObject;
use Vanguard\Classes\ExcelWrapper;
use Vanguard\Models\Import;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Modules\Airtable\AirtableApi;
use Vanguard\Modules\Airtable\AirtableImporter;
use Vanguard\Modules\Jira\JiraApiModule;
use Vanguard\Modules\Salesforce\SalesforceApiModule;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableReferRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\FileService;
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
    protected $storeAirtableRowsCount = 0;

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
        $this->tableDataService = new TableDataService(false);
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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '1200');

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
        } //TRANSPOSE IMPORT
        elseif ($this->data['import_type'] === 'transpose_import' && !empty($this->data['transpose_item'])) {
            $this->transposeImport();
        } //JIRA IMPORT
        elseif ($this->data['import_type'] === 'jira_import' && !empty($this->data['jira_item'])) {
            $this->jiraImport();
        } //SALESFORCE IMPORT
        elseif ($this->data['import_type'] === 'salesforce_import' && !empty($this->data['salesforce_item'])) {
            $this->salesforceImport();
        }

        //recalc all formulas if table has them
        $this->tableDataService->newVersionWatcher = true;
        $this->tableDataService->newTableVersion($this->table);
        $this->tableDataService->recalcTableFormulas($this->table, $this->table->user_id);
        dispatch(new WatchMirrorValues($this->table->id));
        dispatch(new WatchRemoteFiles($this->table->id));

        Import::where('id', '=', $this->import_id)->update([
            'status' => 'done'
        ]);
    }

    /**
     * @throws Exception
     */
    protected function CsvExcelImport()
    {
        $hdrRowNum = floatval($this->data['csv_settings']['headerRowNum'] ?? 0);
        $startRowNum = floatval($this->data['csv_settings']['startRow'] ?? 0);
        $start = $startRowNum ?: $hdrRowNum;
        $end = floatval($this->data['csv_settings']['endRow'] ?? 0);

        $ext = $this->data['csv_settings']['extension']
            ?? strtolower(pathinfo($this->data['csv_settings']['filename'], PATHINFO_EXTENSION));
        if ($ext == 'csv') {
            $this->csvImport($start, $end, $this->data['csv_settings']['filename']);
        } elseif ($ext == 'xls' || $ext == 'xlsx') {
            $this->excelImport($start, $end);
        } else {
            Log::channel('jobs')->info('Csv/Excel Import - incorrect extension!');
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

            Import::where('id', '=', $this->import_id)->update([
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

                $pack[] = $this->getImportFromDataCols($data, 'CSV');
            }

            $this->insertPack($pack, 'CSV');
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
     * @param array $col
     * @param $val
     * @return array|string|string[]|null
     */
    protected function dataConverter(array $col, $val)
    {
        if (in_array($col['f_type'], ['Date', 'Date Time'])) {
            //prevent 0000-00-00 on importing
            if (!$val) {
                $res = null;
            } else {
                $dtz = new DateTimeZone($this->user->timezone ?: 'UTC');
                try {
                    $res = new DateTime($val, $dtz);
                } catch (Exception $e) {
                    $res = null;
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

            Import::where('id', '=', $this->import_id)->update([
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

                $pack[] = $this->getImportFromDataCols($data, 'Excel');
            }

            $this->insertPack($pack, 'Excel');
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

                Import::where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
                ]);

                $pack = [];
                foreach ($all_rows as $row) {
                    $pack[] = $this->getImportFromDataCols((array)$row, 'MySQL');
                }
                $this->insertPack($pack, 'MySQL');
            }

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - MySql General Error');
            Log::channel('jobs')->info($e->getMessage());
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
            $info = ['added' => 0, 'updated' => 0, 'deleted' => 0];
            $db_name = $this->table->db_name;

            //present Documents for copying
            $documents = [];
            foreach ($reference->_reference_corrs as $corrs) {
                if ($corrs->_ref_field->f_type == 'Attachment' && $corrs->_field->f_type == 'Attachment') {
                    $documents[$corrs->_ref_field->id] = $corrs->_field->id;
                }
            }

            //del old records
            $info['deleted'] = DB::connection('mysql_data')->table($db_name)->where('refer_tb_id', '=', $ref_table->id)->count();
            DB::connection('mysql_data')->table($db_name)->where('refer_tb_id', '=', $ref_table->id)->delete();

            //get query
            $query = (new TableDataQuery($ref_table));
            if ($reference->ref_row_group_id) {
                $query->applySelectedRowGroup($reference->ref_row_group_id);
            }
            $query = $query->getQuery();

            //import rows from referenced table
            $lines = $query->count();
            $info['added'] = $lines;
            for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

                $all_rows = $query->offset($cur * $this->pack_len)
                    ->limit($this->pack_len)
                    ->get()
                    ->toArray();

                Import::where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
                ]);

                $pack = [];

                foreach ($all_rows as $row) {
                    $insert = $this->setDefaults($this->data['columns']);

                    foreach ($reference->_reference_corrs as $corrs) {
                        try {
                            $insert[$corrs->_field->field] = $row[$corrs->_ref_field->field] ?? null;
                        } catch (Exception $e) {
                            Log::channel('jobs')->info('ImportTableData - Reference Preparing Error');
                            Log::channel('jobs')->info($e->getMessage());
                        }
                    }

                    $insert['refer_tb_id'] = $ref_table->id;
                    $insert = $this->setCreatedAndModif($insert);

                    $pack[] = $insert;
                }

                try {
                    $old_ids = Arr::pluck($all_rows, 'id');
                    $new_ids = $this->tableDataService->insertMass($this->table, $pack);

                    if (count($old_ids) == count($new_ids) && $documents) {
                        (new FileService())->copyAttachForRowsSpecial($ref_table, $this->table, array_combine($old_ids, $new_ids), $documents);
                    }

                    (new TableRepository())->onlyUpdateTable($this->table->id, [
                        'num_rows' => $this->table->num_rows + count($pack)
                    ]);
                } catch (Exception $e) {
                    Log::channel('jobs')->info('ImportTableData - Reference Saving Error');
                    Log::channel('jobs')->info($e->getMessage());
                }
            }

            $info['updated'] = min($info['added'], $info['deleted']);
            $info['added'] = min($info['added'] - $info['updated'], 0);
            $info['deleted'] = min($info['deleted'] - $info['updated'], 0);

            Import::where('id', '=', $this->import_id)->update([
                'info' => $this->infoImportDataHtml($info),
            ]);
            sleep(3);

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Reference General Error');
            Log::channel('jobs')->info($e->getMessage());
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

        Import::where('id', '=', $this->import_id)->update([
            'complete' => 10,
        ]);

        foreach ($strings as $row_idx => $row) {
            //skip first row as Headers.
            if ($row_idx == 0 && $this->data['paste_settings']['f_header']) {
                continue;
            }

            $row_cells = $this->service->pastedDataParser($row);
            $insert = $this->getImportFromDataCols($row_cells, 'Paste Data');

            $repl_values = $this->data['paste_settings']['replace_values'];
            if (!empty($repl_values) && is_array($repl_values)) {
                $insert = array_merge($insert, $repl_values);
            }

            $pack[] = $insert;
        }

        Import::where('id', '=', $this->import_id)->update([
            'complete' => 50,
        ]);

        $this->insertPack($pack, 'Paste Data');
    }

    /**
     * Transpose Import
     *
     * transpose_item: [
     *      direction: 'direct'/'reverse',
     *      skip_empty: bool,
     *      source_tb_id: int,
     *      row_group_id: int,
     *      grouper_field_id: int,
     *      reverse_val_field_id: int,
     * ],
     * (direct/reverse transpose) colums: [
     *      trps_type: 'inheritance'/'field_name'/'field_values'/'reverse_group',
     *      trps_value: int or string/null/array of int or string,
     * ]
     */
    protected function transposeImport()
    {
        try {
            $tItem = $this->data['transpose_item'];
            $isDirect = $tItem['direction'] == 'direct';
            $refTable = (new TableRepository())->getTable($tItem['source_tb_id']);

            $query = (new TableDataQuery($refTable));
            if (!empty($tItem['row_group_id'])) {
                $query->applySelectedRowGroup($tItem['row_group_id']);
            }
            $query = $query->getQuery();

            if ($isDirect) {
                $this->transposeImportDirect($query, $refTable);
            } else {
                $this->transposeImportReverse($query, $refTable);
            }

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Transpose General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param Builder $query
     * @param Table $refTable
     * @return void
     */
    protected function transposeImportDirect(Builder $query, Table $refTable): void
    {
        try {
            $tItem = $this->data['transpose_item'];

            $columnCollection = collect($this->data['columns']);
            $nameFld = $columnCollection->filter(function($col) {
                return $col['trps_type'] == 'field_name';
            })->first();
            $valueFld = $columnCollection->filter(function($col) {
                return $col['trps_type'] == 'field_values';
            })->first();

            if (! $nameFld || ! $valueFld) {
                return;
            }

            //transpose rows from source table
            $lines = $query->count();
            for ($cur = 0; ($cur * $this->pack_len) < $lines; $cur++) {

                $all_rows = $query->offset($cur * $this->pack_len)
                    ->limit($this->pack_len)
                    ->get()
                    ->toArray();

                Import::where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * $this->pack_len) / $lines) * $this->pack_len)
                ]);

                $pack = [];
                foreach ($all_rows as $row) {
                    $insert = $this->setDefaults($this->data['columns']);
                    $insert = $this->setCreatedAndModif($insert);

                    //Inheritances
                    foreach ($this->data['columns'] as $col) {
                        if (($col['trps_type'] ?? '') == 'inheritance' && !empty($col['trps_value'])) {
                            $refFld = $refTable->_fields
                                ->filter(function ($f) use ($col) {
                                    return $f->id == $col['trps_value'] || $f->name == $col['trps_value'];
                                })
                                ->first();
                            $insert[$col['field']] = $row[$refFld->field] ?? null;
                        }
                    }

                    //Transpose
                    $trpsFields = $refTable->_fields
                        ->filter(function ($f) use ($valueFld) {
                            return in_array($f->id, $valueFld['trps_value'])
                                || in_array($f->name, $valueFld['trps_value']);
                        });
                    foreach ($trpsFields as $refFld) {
                        $insert[$nameFld['field']] = $refFld->name;
                        $insert[$valueFld['field']] = $row[$refFld->field] ?? null;
                        if (!$tItem['skip_empty'] || $insert[$valueFld['field']]) {
                            $pack[] = $insert;
                        }
                    }
                }

                $this->insertPack($pack, 'Transpose');
            }
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Transpose DIRECT General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param Builder $query
     * @param Table $refTable
     * @return void
     */
    protected function transposeImportReverse(Builder $query, Table $refTable): void
    {
        try {
            $tItem = $this->data['transpose_item'];

            $grouperValues = collect($this->data['columns'])
                ->filter(function($col) {
                    return $col['trps_type'] == 'reverse_group';
                });

            $grouperFld = $refTable->_fields->where('id', $tItem['grouper_field_id'])->first();
            $valueFld = $refTable->_fields->where('id', $tItem['reverse_val_field_id'])->first();

            if (! $grouperValues->count() || ! $grouperFld || ! $valueFld) {
                return;
            }

            $trpsValues = collect($this->data['columns'])
                ->where('trps_type', 'inheritance')
                ->pluck('trps_value')
                ->toArray();
            $inheritanceFields = $refTable->_fields
                ->filter(function ($f) use ($trpsValues) {
                    return in_array($f->id, $trpsValues)
                        || in_array($f->name, $trpsValues);
                })
                ->pluck('field');

            if ($inheritanceFields->count()) {
                (clone $query)
                    ->distinct()
                    ->select($inheritanceFields)
                    ->chunk(50, function ($distincts) use ($refTable, $inheritanceFields, $query, $grouperFld, $valueFld, $grouperValues) {
                        foreach ($distincts as $distinctRow) {
                            $groupQuery = clone $query;
                            foreach ($inheritanceFields as $inheritanceField) {
                                $groupQuery->where($inheritanceField, $distinctRow[$inheritanceField]);
                            }
                            $this->transposeImportReverseFillGroup($refTable, $groupQuery, $grouperFld, $valueFld, $grouperValues);
                        }
                    });
            } else {
                $this->transposeImportReverseFillGroup($refTable, $query, $grouperFld, $valueFld, $grouperValues);
            }

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Transpose REVERSE General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param Table $refTable
     * @param Builder $query
     * @param TableField $grouperFld
     * @param TableField $valueFld
     * @param $grouperValues
     * @return void
     */
    protected function transposeImportReverseFillGroup(Table $refTable, Builder $query, TableField $grouperFld, TableField $valueFld, $grouperValues): void
    {
        try {
            $rows = $query->get();
            $firstRow = $rows->first();
            $idx = 0;
            $changes = true;

            $pack = [];
            while ($changes) {
                $insert = $this->setDefaults($this->data['columns']);
                $insert = $this->setCreatedAndModif($insert);

                //Inheritances
                foreach ($this->data['columns'] as $col) {
                    if (($col['trps_type'] ?? '') == 'inheritance' && !empty($col['trps_value'])) {
                        $refFld = $refTable->_fields
                            ->filter(function ($f) use ($col) {
                                return $f->id == $col['trps_value'] || $f->name == $col['trps_value'];
                            })
                            ->first();
                        $insert[$col['field']] = $firstRow[$refFld->field] ?? null; //First row can be used as we have distinct chunks forInheritances
                    }
                }

                //Transpose
                $changes = false;
                foreach ($grouperValues as $i => $col) {
                    $found = $rows->where($grouperFld->field, $col['trps_value'])->values();
                    $found = $found[$idx] ?? null;
                    if ($found) {
                        $changes = true;
                        $insert[$col['field']] = $found[$valueFld->field] ?? null;
                    }
                }

                if ($changes) {
                    $pack[] = $insert;
                    $idx++;
                }
            }

            $this->insertPack($pack, 'Transpose');

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Transpose REVERSE GROUP General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    // ^^^ Transpose ^^^

    /**
     * Jira Import
     *
     * jira_item: [
     *      action: 'sync'/'import',
     *      cloud_id: int,
     *      project_names: array of strings,
     *      jql_query: string, - used this or above attribute
     *      remove_not_found: int,
     *      add_new_records: int,
     *      update_changed: int,
     * ],
     */
    protected function jiraImport()
    {
        try {
            $startTime = now();

            $input = $this->data['jira_item'] ?? [];
            $projects = $input['project_names'] ?? [];
            $jql = $input['jql_query'] ?? '';
            $curCount = 0;
            $maxCounts = count($projects) + ($jql ? 1 : 0);
            $info = ['added' => 0, 'updated' => 0, 'deleted' => 0];

            foreach ($projects as $pName) {
                $info = $this->jiraImportProject('project="'.$pName.'"', $curCount, $maxCounts, $info);
                $curCount += 1;
            }
            if ($jql) {
                $info = $this->jiraImportProject($jql, $curCount, $maxCounts, $info);
                $curCount += 1;
            }

            if ($input['action'] == 'sync' && $input['remove_not_found']) {
                $this->removeNotFoundSyncs($startTime, $info);
            }

            sleep(3);

            (new TableRepository())->onlyUpdateTable($this->table->id, [
                'import_last_jira_action' => now($this->user->timezone ?: 'UTC'),
            ]);
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Jira General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param string $jql
     * @param int $curCount
     * @param int $maxCounts
     * @param array $info
     * @return array
     * @throws Exception
     */
    protected function jiraImportProject(string $jql, int $curCount, int $maxCounts, array $info): array
    {
        $input = $this->data['jira_item'];
        $isSync = $input['action'] == 'sync';
        $canAdd = !$isSync || $input['add_new_records'];
        $canUpdate = !$isSync || $input['update_changed'];
        $shouldDelete = $input['remove_not_found'] ?? false;

        $api = new JiraApiModule();
        $tdq = new TableDataQuery($this->table);
        $rowRepo = new TableDataRepository();
        $startAt = 0;
        do {
            $lastAction = $isSync && !$shouldDelete ? $this->table->import_last_jira_action : '';
            $issues = $api->issues($input['cloud_id'], $jql, $startAt, $lastAction ?: '');

            $total = $issues['total'];
            $issues = $issues['issues'];

            $pack = [];
            foreach ($issues as $iss) {
                $row = null;
                if ($isSync) {
                    $tdq->clearQuery();
                    $row = $tdq->getQuery()->where('request_id', '=', $this->jiraIssKey($iss))->first();
                }

                if ( (!$row && !$canAdd) || ($row && !$canUpdate) ) {
                    continue; //Skip if "add" or "update" is not available
                }

                if ($row) {
                    $rowRepo->quickUpdate($this->table, $row['id'], $this->getImportFromDataCols($iss, 'Jira'));
                    $info['updated'] += 1;
                } else {
                    $pack[] = array_merge(
                        ['request_id' => $this->jiraIssKey($iss)],//For syncing ^
                        $this->getImportFromDataCols($iss, 'Jira')
                    );
                    $info['added'] += 1;
                }
            }
            if ($pack) {
                $this->insertPack($pack, 'Jira');
            }

            $startAt += 100;
            if ($startAt > $total) {
                $startAt = $total;
            }

            $complete = (int)(($curCount / $maxCounts) * 100);
            if ($total) {
                $complete += (int)(($startAt / $total / $maxCounts) * 100);
            }

            Import::where('id', '=', $this->import_id)->update([
                'complete' => $complete,
                'info' => $this->infoImportDataHtml($info),
            ]);
            usleep(100 * 1000);//100ms

        } while ($startAt < $total);

        return $info;
    }

    /**
     * @param array $iss
     * @return string
     */
    protected function jiraIssKey(array $iss): string
    {
        return $iss['id'] . ':' . Str::limit($iss['key'], 10, '');
    }

    /**
     * Salesforce Import
     *
     * salesforce_item: [
     *      action: 'sync'/'import',
     *      cloud_id: int,
     *      object_name: string,
     *      object_id: int,
     *      remove_not_found: int,
     *      add_new_records: int,
     *      update_changed: int,
     * ],
     */
    protected function salesforceImport()
    {
        try {
            $startTime = now();

            $input = $this->data['salesforce_item'] ?? [];
            $info = ['added' => 0, 'updated' => 0, 'deleted' => 0];

            if ($input['cloud_id'] && $input['object_id']) {
                $info = $this->salesforceImportObject($input, $info);
            }

            if ($input['action'] == 'sync' && $input['remove_not_found']) {
                $this->removeNotFoundSyncs($startTime, $info);
            }

            sleep(3);

            (new TableRepository())->onlyUpdateTable($this->table->id, [
                'import_last_salesforce_action' => now($this->user->timezone ?: 'UTC'),
            ]);
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Salesforce General Error');
            Log::channel('jobs')->info($e->getMessage());
            $this->failed();
        }
    }

    /**
     * @param string $jql
     * @param int $curCount
     * @param int $maxCounts
     * @param array $info
     * @return array
     * @throws Exception
     */
    protected function salesforceImportObject(array $input, array $info): array
    {
        $isSync = $input['action'] == 'sync';
        $canAdd = !$isSync || $input['add_new_records'];
        $canUpdate = !$isSync || $input['update_changed'];
        $shouldDelete = $input['remove_not_found'] ?? false;

        $api = new SalesforceApiModule();
        $tdq = new TableDataQuery($this->table);
        $rowRepo = new TableDataRepository();
        $startAt = 0;
        do {
            $lastAction = $isSync && !$shouldDelete ? $this->table->import_last_salesforce_action : '';
            $response = $api->objectRows($input['cloud_id'], $input['object_id'], $startAt, $lastAction ?: '');

            $total = $response['totalSize'] ?? 100;
            $records = $response['records'] ?? [];

            $pack = [];
            foreach ($records as $record) {
                $row = null;
                if ($isSync) {
                    $tdq->clearQuery();
                    $row = $tdq->getQuery()->where('request_id', '=', $this->salesforceId($record))->first();
                }

                if ( (!$row && !$canAdd) || ($row && !$canUpdate) ) {
                    continue; //Skip if "add" or "update" is not available
                }

                if ($row) {
                    $rowRepo->quickUpdate($this->table, $row['id'], $this->getImportFromDataCols($record, 'Salesforce'));
                    $info['updated'] += 1;
                } else {
                    $pack[] = array_merge(
                        ['request_id' => $this->salesforceId($record)],//For syncing ^
                        $this->getImportFromDataCols($record, 'Salesforce')
                    );
                    $info['added'] += 1;
                }
            }
            if ($pack) {
                $this->insertPack($pack, 'Salesforce');
            }

            $startAt += 100;
            if ($startAt > $total) {
                $startAt = $total;
            }

            Import::where('id', '=', $this->import_id)->update([
                'complete' => (int)(($startAt / $total) * 100),
                'info' => $this->infoImportDataHtml($info),
            ]);
            usleep(100 * 1000);//100ms

        } while ($startAt < $total);

        return $info;
    }

    /**
     * @param array $row
     * @return string
     */
    protected function salesforceId(array $row): string
    {
        return Str::limit($row['Id'], 32, '');
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

                $pack[] = $this->getImportFromDataCols($row, 'Google Sheets');

                if (count($pack) == $this->pack_len || $row_idx == ($lines - 1)) {
                    Import::where('id', '=', $this->import_id)->update([
                        'complete' => (int)(($row_idx / $lines) * 100),
                    ]);
                    $this->insertPack($pack, 'Google Sheets');
                    $pack = [];
                }
            }
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Google Sheets General Error');
            Log::channel('jobs')->info($e->getMessage());
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

                $pack[] = $this->getImportFromDataCols($row, 'Web Scrap');

                if (count($pack) == $this->pack_len || $row_idx == ($lines - 1)) {
                    Import::where('id', '=', $this->import_id)->update([
                        'complete' => (int)(($row_idx / $lines) * 100),
                    ]);
                    $this->insertPack($pack, 'Web Scrap');
                    $pack = [];
                }

            }

        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Web Scrap General Error');
            Log::channel('jobs')->info($e->getMessage());
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
            Log::channel('jobs')->info('ImportTableData - Airtable General Error');
            Log::channel('jobs')->info($e->getMessage());
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
                        Log::channel('jobs')->info('ImportTableData - Chart Export Insert Error');
                        Log::channel('jobs')->info($e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Chart General Error');
            Log::channel('jobs')->info($e->getMessage());
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
                        Log::channel('jobs')->info('ImportTableData - Airtable Preparing Error');
                        Log::channel('jobs')->info($e->getMessage());
                    }
                }
            }
            $insert = $this->setCreatedAndModif($insert);

            try {
                $new_id = $this->tableDataService->insertRow($this->table, $insert, $this->table->user_id);
                $importer->moveAttachRowToArray($new_id);
                $pack++;
            } catch (Exception $e) {
                Log::channel('jobs')->info('ImportTableData - Airtable Insert Error');
                Log::channel('jobs')->info($e->getMessage());
            }
        }

        $this->storeAirtableRowsCount += 5;
        Import::where('id', '=', $this->import_id)->update([
            'complete' => min(99, $this->storeAirtableRowsCount),
        ]);

        //DOWNLOAD AND SAVE ATTACHMENTS
        try {
            $importer->saveAttachmentsFiles($this->table);
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - Airtable General Error');
            Log::channel('jobs')->info($e->getMessage());
        }
    }

    /**
     * @param array $pack
     * @param string $msg
     * @return void
     */
    protected function insertPack(array $pack, string $msg)
    {
        try {
            $this->tableDataService->insertMass($this->table, $pack);
            (new TableRepository())->onlyUpdateTable($this->table->id, [
                'num_rows' => $this->table->num_rows + count($pack)
            ]);
        } catch (Exception $e) {
            Log::channel('jobs')->info('ImportTableData - '.$msg.' Saving Error');
            Log::channel('jobs')->info($e->getMessage());
        }
    }

    /**
     * @param $sourceData
     * @param string $msg
     * @return array
     */
    protected function getImportFromDataCols($sourceData, string $msg): array
    {
        $insert = $this->setDefaults($this->data['columns']);
        //fill only those data columns which numbers are setted in 'table columns settings'
        foreach ($this->data['columns'] as $col) {
            if (!in_array($col['field'], $this->service->system_fields)) {
                try {
                    switch ($this->data['import_type']) {
                        case 'jira_import': $present_val = JiraApiModule::fromIssueReceive($sourceData, $col['last_col_corr'] ?? '');
                            break;
                        case 'salesforce_import': $present_val = trim($sourceData[$col['last_col_corr'] ?? ''] ?? '');
                            break;
                        default: $present_val = trim($sourceData[$col['col'] ?? null] ?? '');
                            break;
                    }

                    $insert[$col['field']] = $this->dataConverter($col, $present_val);
                } catch (Exception $e) {
                    Log::channel('jobs')->info('ImportTableData - '.$msg.' Source Importing Error');
                    Log::channel('jobs')->info($e->getMessage());
                }
            }
        }
        return $this->setCreatedAndModif($insert);
    }

    /**
     * @param array $info
     * @return string
     */
    protected function infoImportDataHtml(array $info): string
    {
        $str = '';
        if ($info['updated'] > 0) {
            $str .= '<div>'.$info['updated'].' records have been updated.</div>';
        }
        if ($info['added'] > 0) {
            $str .= '<div>'.$info['added'].' new records have been imported.</div>';
        }
        if ($info['deleted'] > 0) {
            $str .= '<div>'.$info['deleted'].' records(not found in source) have been removed.</div>';
        }
        return $str;
    }

    /**
     * @param $time
     * @param array $info
     * @return array
     */
    protected function removeNotFoundSyncs($time, array $info): array
    {
        $repo = new TableDataRepository();

        $info['deleted'] = $repo->getTDQ($this->table)
            ->where('updated_at', '<', $time)
            ->count();

        $repo->getTDQ($this->table)
            ->where('updated_at', '<', $time)
            ->delete();

        Import::where('id', '=', $this->import_id)->update([
            'info' => $this->infoImportDataHtml($info),
        ]);

        return $info;
    }
}
