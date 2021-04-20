<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\HtmlXmlService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class ImportTableData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    private $table;
    private $tableDataService;
    private $user;
    private $import_id;
    private $htmlservice;
    private $service;

    /**
     * ImportTableData constructor.
     *
     * @param Table $table
     * @param array $data
     * @param User $user
     * @param $import_id
     */
    public function __construct(Table $table, Array $data, User $user, $import_id)
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
        \ini_set('memory_limit', '256M');

        $is_admin = $this->user ? $this->user->isAdmin() : false;

        //CSV IMPORT
        if ($this->data['import_type'] === 'csv' && !empty($this->data['csv_settings']['filename'])) {
            $this->csvImport();
        }
        //MYSQL IMPORT
        elseif ($this->data['import_type'] === 'mysql' && !empty($this->data['mysql_settings']['connection_id'])) {
            $this->mysqlImport();
        }
        //REFERENCE IMPORT
        elseif ($this->data['import_type'] === 'reference' && !empty($this->data['referenced_table'])) {
            $this->referenceImport();
        }
        //PASTE IMPORT
        elseif ($this->data['import_type'] === 'paste' && !empty($this->data['paste_file'])) {
            $this->pasteImport();
        }
        //GOOGLE SHEET IMPORT
        elseif ($this->data['import_type'] === 'g_sheet' && !empty($this->data['g_sheet'])) {
            $this->googleSheetImport();
        }
        //GOOGLE SHEET IMPORT
        elseif ($this->data['import_type'] === 'web_scrap' && !empty($this->data['html_xml'])) {
            $this->googleWebScrapImport();
        }

        //recalc all formulas if table has them
        $this->tableDataService->recalcTableFormulas($this->table);

        DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
            'status' => 'done'
        ]);
    }


    /**
     * CSV Data Import
     */
    public function csvImport()
    {
        $fp = new \SplFileObject(storage_path("app/csv/" . $this->data['csv_settings']['filename']), 'r');
        $fp->seek(PHP_INT_MAX);
        $lines = $fp->key() + 1;
        $fp->rewind();

        //CSV IMPORT
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

        for ($cur = 0; ($cur * 100) < $lines; $cur++) {

            DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                'complete' => (int)((($cur * 100) / $lines) * 100)
            ]);

            $pack = [];

            for ($i = 0; $i < 100; $i++) {
                $data = $fp->fgetcsv();

                if ($fp->eof()) {
                    break;
                }

                if (($cur * 100 + $i) < $start || ($end && ($cur * 100 + $i) > $end)) {
                    continue;
                }

                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    $present_val = trim($data[$col['col'] ?? null] ?? '');
                    if ($present_val && !in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $insert[$col['field']] = substr($this->dataConverter($data, $col), 0, $this->colSize($col));
                        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                \Log::info('ImportTableData - CSV Import Error');
                \Log::info($e->getMessage());
            }
        }

        unset($fp);
        Storage::delete("csv/" . $this->data['csv_settings']['filename']);
    }

    /**
     * Set Defaults for Insert.
     *
     * @param array $data_columns
     * @return array
     */
    public function setDefaults(array $data_columns)
    {
        $fields = [];
        foreach ($data_columns as $col) {
            $fields[$col['field']] = null;
        }
        return $this->tableDataService->setDefaults($this->table, $fields, $this->user->id);
    }

    /**
     * Convert some datas to Special Formats.
     *
     * @param array $data
     * @param array $col
     * @return bool|\DateTime|mixed|null
     */
    public function dataConverter(array $data, array $col)
    {
        if (in_array($col['f_type'], ['Date', 'Date Time'])) {
            //prevent 0000-00-00 on importing
            if ($data[$col['col']] === '') {
                $res = null;
            } else {
                try {
                    $res = new \DateTime($data[$col['col']]);
                } catch (\Exception $e) {
                }
                if (empty($res)) {
                    $res = \DateTime::createFromFormat('d/m/Y', $data[$col['col']]);
                }
                if (empty($res)) {
                    $res = \DateTime::createFromFormat('M j, Y', $data[$col['col']]);
                }
                $res = !empty($res) ? $res->format('Y/m/d H:i:s') : null;
            }
        } elseif (in_array($col['f_type'], ['Currency', 'Integer', 'Decimal', 'Percentage'])) {
            $res = preg_replace('/[^\d\.-]/i', '', $data[$col['col']]);
        } else {
            $res = $data[$col['col']];
        }
        return $res;
    }

    /**
     * Get Column Size.
     *
     * @param array $col
     * @return int
     */
    public function colSize(array $col)
    {
        switch ($col['f_type']) {
            case 'Integer':
            case 'String':
                $res = $col['f_size'] ?: 255;
                break;
            case 'Decimal':
            case 'Currency':
            case 'Percentage':
                $res = str_replace(',', '.', $col['f_size']);
                $res = array_sum(explode('.', $col['f_size'])) + 1;
                $res = $res > 1 ? $res : 255;
                break;
            default:
                $res = 255;
                break;
        }
        return $res;
    }

    /**
     * MySQL Data Import
     */
    public function mysqlImport()
    {
        $this->service->configRemoteConnection($this->data['mysql_settings']);

        try {
            $lines = DB::connection('mysql_remote')->table($this->data['mysql_settings']['table'])->count();

            for ($cur = 0; ($cur * 100) < $lines; $cur++) {

                $all_rows = DB::connection('mysql_remote')
                    ->table($this->data['mysql_settings']['table'])
                    ->offset($cur * 100)
                    ->limit(100)
                    ->get();

                DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                    'complete' => (int)((($cur * 100) / $lines) * 100)
                ]);

                $pack = [];

                foreach ($all_rows as $row) {
                    $row = (array)$row;
                    $insert = $this->setDefaults($this->data['columns']);

                    foreach ($this->data['columns'] as $col) {
                        $present_val = trim($row[$col['col'] ?? null] ?? '');
                        if ($present_val && !in_array($col['field'], $this->service->system_fields)) {
                            try {
                                $insert[$col['field']] = substr($this->dataConverter($row, $col), 0, $this->colSize($col));
                            } catch (\Exception $e) {
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
                } catch (\Exception $e) {
                    \Log::info('ImportTableData - MySQL Import Error');
                    \Log::info($e->getMessage());
                }
            }

        } catch (\Exception $e) {
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
    public function referenceImport()
    {
        $ref_table = Table::where('id', '=', $this->data['referenced_table']['id'])->first();

        try {
            $db_name = $this->table->db_name;

            if (!$this->data['referenced_table']['only_del']) {

                //del old records
                DB::connection('mysql_data')->table($db_name)->where('refer_tb_id', $ref_table->id)->delete();

                //import rows from referenced table
                $lines = DB::connection('mysql_data')->table($ref_table->db_name)->count();

                for ($cur = 0; ($cur * 100) < $lines; $cur++) {

                    $all_rows = DB::connection('mysql_data')
                        ->table($ref_table->db_name)
                        ->offset($cur * 100)
                        ->limit(100)
                        ->get();

                    DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                        'complete' => (int)((($cur * 100) / $lines) * 100)
                    ]);

                    $pack = [];

                    foreach ($all_rows as $row) {
                        $row = (array)$row;
                        $insert = $this->setDefaults($this->data['columns']);

                        foreach ($this->data['referenced_table']['objects'] as $obj) {
                            try {
                                if (!empty($row[$obj['ref_field_db']])) {
                                    $insert[$obj['table_field_db']] = substr($row[$obj['ref_field_db']], 0, $this->colSize($obj));
                                }
                            } catch (\Exception $e) {
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
                    } catch (\Exception $e) {
                        \Log::info('ImportTableData - Reference Import Error');
                        \Log::info($e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->failed();
        }
    }

    /**
     * Paste Data Import
     */
    public function pasteImport()
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
                $present_val = trim($row_cells[$col['col'] ?? null] ?? '');
                if ($present_val && !in_array($col['field'], $this->service->system_fields)) {
                    try {
                        $insert[$col['field']] = substr($this->dataConverter($row_cells, $col), 0, $this->colSize($col));
                    } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            \Log::info('ImportTableData - Paste Data Import Error');
            \Log::info($e->getMessage());
        }

        Storage::delete('pasted/' . $this->data['paste_file']);
    }

    /**
     * Google Sheet Data Import
     */
    public function googleSheetImport()
    {
        try {

            $token_json = $this->user->_clouds()
                ->where('cloud', 'Google')
                ->whereNotNull('token_json')
                ->first();
            $token_json = $token_json ? $token_json->gettoken() : null;
            $gsheet = $this->data['g_sheet'];
            $strings = $this->service->parseGoogleSheet($gsheet['link'], $gsheet['name'] ?? 'Sheet1', $token_json);
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
                    $present_val = trim($row[$col['col'] ?? null] ?? '');
                    if ($present_val && !in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $insert[$col['field']] = substr($this->dataConverter($row, $col), 0, $this->colSize($col));
                        } catch (\Exception $e) {}
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;

                if (count($pack) == 100 || $row_idx == ($lines-1)) {
                    try {
                        $this->tableDataService->insertMass($this->table, $pack);

                        (new TableRepository())->onlyUpdateTable($this->table->id, [
                            'num_rows' => $this->table->num_rows + count($pack)
                        ]);
                    } catch (\Exception $e) {
                        \Log::info('ImportTableData - Google Sheet Data Import Error');
                        \Log::info($e->getMessage());
                    }
                    $pack = [];
                }

            }

        } catch (\Exception $e) {
            $this->failed();
        }
    }

    /**
     * Web Scraping Data Import
     */
    public function googleWebScrapImport()
    {
        try {

            $htmlxml = $this->data['html_xml'];
            $strings = [];
            if ($htmlxml['url']) {
                if ($htmlxml['xpath']) {
                    $strings = $this->htmlservice->parseXpathHtml($htmlxml['url'] ?? '', $htmlxml['xpath'] ?? '', true);
                } else {
                    $strings = $this->htmlservice->parsePageHtml($htmlxml['url'] ?? '', $htmlxml['query'] ?? '', $htmlxml['index'] ?? '', true);
                }
            }
            if ($htmlxml['xml_url']) {
                $strings = $this->htmlservice->parseXmlPage($htmlxml['xml_url'] ?? '', $htmlxml['xml_xpath'] ?? '', true);
            }
            $lines = count($strings);

            $pack = [];
            foreach ($strings as $row_idx => $row) {
                $insert = $this->setDefaults($this->data['columns']);
                //fill only those data columns which numbers are setted in 'table columns settings'
                foreach ($this->data['columns'] as $col) {
                    $present_val = trim($row[$col['col'] ?? null] ?? '');
                    if ($present_val && !in_array($col['field'], $this->service->system_fields)) {
                        try {
                            $insert[$col['field']] = substr($this->dataConverter($row, $col), 0, $this->colSize($col));
                        } catch (\Exception $e) {}
                    }
                }
                $insert = $this->setCreatedAndModif($insert);

                $pack[] = $insert;

                if (count($pack) == 100 || $row_idx == ($lines-1)) {
                    try {
                        $this->tableDataService->insertMass($this->table, $pack);

                        (new TableRepository())->onlyUpdateTable($this->table->id, [
                            'num_rows' => $this->table->num_rows + count($pack)
                        ]);
                    } catch (\Exception $e) {
                        \Log::info('ImportTableData - Web Scraping Data Import Error');
                        \Log::info($e->getMessage());
                    }
                    $pack = [];
                }

            }

        } catch (\Exception $e) {
            $this->failed();
        }
    }

    /**
     * @param array $insert
     * @return array
     */
    private function setCreatedAndModif(array $insert)
    {
        $insert['created_by'] = $this->user->id;
        $insert['created_on'] = now();
        $insert['modified_by'] = $this->user->id;
        $insert['modified_on'] = now();
        return $insert;
    }
}
