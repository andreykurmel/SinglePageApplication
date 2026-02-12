<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Repositories\Tablda\AutomationHistoryRepository;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\ImportRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Support\Excel\ArrayExport;

class CloudBackuper
{
    /**
     * @var bool
     */
    protected $with_app_name = false;
    /**
     * @var DropBoxStrategy|GoogleStrategy|OneDriveStrategy
     */
    protected $sender_strategy;
    /**
     * @var TableBackup
     */
    protected $backup;
    /**
     * @var ImportRepository
     */
    protected $importRepo;
    /**
     * @var AutomationHistoryRepository
     */
    protected $automationHistory;
    /**
     * @var array
     * @link TableDataRowsRepository::getRows()
     */
    protected $backupRows = [];
    /**
     * @var array
     */
    protected $viewCols = [];
    /**
     * @var array
     */
    protected $viewColIds = [];

    /**
     * CloudBackuper constructor.
     * @param TableBackup $backup
     * @throws \Exception
     */
    public function __construct(TableBackup $backup)
    {
        switch ($backup->_cloud->cloud) {
            case 'Google':
                $this->sender_strategy = new GoogleStrategy($backup->_cloud);
                $this->with_app_name = true;
                break;
            case 'Dropbox':
                $this->sender_strategy = new DropBoxStrategy($backup->_cloud);
                $this->with_app_name = false;
                break;
            case 'OneDrive':
                $this->sender_strategy = new OneDriveStrategy($backup->_cloud);
                $this->with_app_name = true;
                break;
            default:
                throw new \Exception('CloudBackuper:Undefined strategy type');
        }
        $this->backup = $backup;
        $this->importRepo = new ImportRepository('CloudBackuper');
        $this->automationHistory = new AutomationHistoryRepository($backup->user_id, $backup->table_id);

        $this->setBkpRows();
    }

    /**
     * setBkpRows
     */
    protected function setBkpRows()
    {
        if (!$this->backup->_table) {
            return;
        }

        $data = [
            'table_id' => $this->backup->_table->id,
            'page' => 1,
            'rows_per_page' => 0,
            'force_execute' => true,
        ];
        if ($this->backup->_table_view) {
            $specials = [
                'view_hash' => $this->backup->_table_view->hash,
            ];
            $data['special_params'] = $specials;

            $permis = TableRights::permissions($this->backup->_table, $specials);
            $view_cols = (new TableService())->findViewCols($this->backup->_table_view->hash);
            $this->viewCols = array_unique( array_intersect($permis->view_fields->toArray(), $view_cols['visible']) );
            foreach ($this->backup->_table->_fields as $fld) {
                if (in_array($fld->field, $this->viewCols)) {
                    $this->viewColIds[] = $fld->id;
                }
            }
        }
        $result = (new TableDataRepository())->getRows($data, $this->backup->_table->user_id);
        $this->backupRows = $result['rows'];
    }

    /**
     * Send Table and Attachments to CloudStorage
     */
    public function sendToCloud()
    {
        $this->automationHistory->startTimer();

        $table_name = $this->backup->_table ? (new FileRepository())->getStorageTable($this->backup->_table, true) : '';
        $ymd = date('Ymd', time());
        $backup_name = !$this->backup->overwrite
            ? $table_name . '_' . $ymd
            : $table_name;

        if ($this->backup->mysql) {
            try {
                //create mysql dump
                $dbname = $this->dataRangeDbName();
                $file_p =  storage_path('tmp/' . $backup_name . '.sql');
                exec('export MYSQL_PWD='.env('DB_PASSWORD').' ; '
                    . '/usr/bin/mysqldump -h '.env('DB_HOST').' -u root -f --skip-extended-insert ' . env('DB_DATABASE_DATA') . ' '
                    . $dbname . ' --single-transaction --quick > ' . $file_p);
                //upload mysql dump
                $target_p = $this->backup->getsubfolder($this->with_app_name) . '/' . $table_name . '/' . $backup_name . '.sql';
                $this->sender_strategy->upload($file_p, $target_p);
                //remove mysql dump
                exec('rm /var/www/' . $backup_name . '.sql');
                $this->dataRangeRemove($dbname);
            } catch (\Exception $e) {
                Log::channel('jobs')->info('Backup error: "mysql - '.$backup_name.' (bkp id '.$this->backup->id.') - "'.$e->getMessage());
            }
        }

        if ($this->backup->csv) {
            try {
                //create csv
                $this->saveTmpFile($this->backup->_table, $backup_name.'.csv', 'CSV');
                //upload csv
                $target_p = $this->backup->getsubfolder($this->with_app_name) . '/' . $table_name . '/' . $backup_name . '.csv';
                $this->sender_strategy->upload(storage_path('app/tmp/'.$backup_name.'.csv'), $target_p);
                //remove csv
                exec('rm ' . storage_path('app/tmp/'.$backup_name.'.csv'));
            } catch (\Exception $e) {
                Log::channel('jobs')->info('Backup error: "csv - '.$backup_name.' (bkp id '.$this->backup->id.') - "'.$e->getMessage());
            }
        }

        if ($this->backup->attach) {
            $prefx = !$this->backup->overwrite ? $ymd.'_' : '';
            //save attachments
            $files = $this->getFiles();
            $file_ids = $files->pluck('table_field_id')->unique()->toArray();
            $fields = (new TableFieldRepository())->getField( $file_ids );

            $dids = [];
            foreach ($files as $file) {
                if ($this->row_id_ddl($file->row_id)) {
                    $dids[] = $this->get_ddl_id($file->row_id);
                }
            }
            $ddls = (new DDLRepository())->getDDLarray($dids);

            foreach ($files as $file) {
                $f_path = 'public/' . $file->filepath . $file->filename;
                $storage_path = storage_path('app/'.$f_path);
                if (Storage::exists($f_path)) {
                    try {
                        //folder with ${field name}
                        $field_name = $fields->where('id', '=', $file->table_field_id)->first();
                        $field_name = $field_name ? $field_name->alphaName() : '';

                        //if DDL -> folder ${ddl name}(${field name})
                        $rid = $file->row_id . '_';
                        if ($this->row_id_ddl($file->row_id)) {
                            $ddl_f = $ddls->where('id', '=', $this->get_ddl_id($file->row_id))->first();
                            $field_name = $ddl_f ? $ddl_f->name . '_' . $field_name . '_' : '';
                            $rid = '';
                            //save DDL images only when needed setting is active
                            if (!$this->backup->ddl_attach) {
                                continue;
                            }
                        }

                        $target_p = $this->backup->getsubfolder($this->with_app_name) . '/';
                        $target_p .= $table_name . '/' . $field_name . '/' . $prefx . $rid . $file->filename;
                        $this->sender_strategy->upload($storage_path, $target_p);
                    } catch (\Exception $e) {
                        Log::channel('jobs')->info('Backup error: "table - ' . $table_name . ' file:' . $storage_path);
                        Log::channel('jobs')->info('Backup error: ' . $e->getMessage());
                    }
                } else {
                    Log::channel('jobs')->info('Backup warning: file not found - ' . $f_path);
                }
            }
        }

        if ($this->backup->mysql || $this->backup->csv || $this->backup->attach) {
            $this->automationHistory->stopTimerAndSave('Autobackup', $this->backup->name, 'Backup', 'Saving');
        }
    }

    /**
     * @return Collection
     */
    protected function getFiles(): Collection
    {
        $sql = File::where('table_id', '=', $this->backup->_table->id);
        if ($this->backup->_table_view) {
            $ids = Arr::pluck($this->backupRows, 'id') ?: [0];
            $sql->where(function($inner) use ($ids) {
                $inner->whereIn('row_id', $ids);
                $inner->orWhereNull('row_id');
            });
            if ($this->viewColIds) {
                $vids = $this->viewColIds;
                $sql->where(function($inner) use ($vids) {
                    $inner->whereIn('table_field_id', $vids);
                    $inner->orWhereNull('table_field_id');
                });
            }
        }
        return $sql->get() ?: collect([]);
    }

    /**
     * @return string
     */
    protected function dataRangeDbName(): string
    {
        if ($this->backup->_table_view) {
            $tmptable = $this->backup->_table->db_name . '_tmpbkp';
            $this->importRepo->deleteTableInDb($tmptable);

            $rowsAndColumns = [
                'rows' => Arr::pluck($this->backupRows, 'id') ?: [0],
                'columns' => $this->viewCols,
            ];

            $this->importRepo->copyTableInDB($this->backup->_table, $rowsAndColumns, $tmptable, true);
            return $tmptable;
        } else {
            return $this->backup->_table->db_name;
        }
    }

    /**
     * @param string $dbname
     */
    protected function dataRangeRemove(string $dbname)
    {
        if ($this->backup->_table_view) {
            $this->importRepo->deleteTableInDb($dbname);
        }
    }

    /**
     * @param $row_id
     * @return int
     */
    protected function row_id_ddl($row_id)
    {
        return preg_match('/ddl_/i', $row_id);
    }

    /**
     * @param $row_id
     * @return mixed|string
     */
    protected function get_ddl_id($row_id)
    {
        $m = [];
        preg_match('/_([\d]*)_/i', $row_id, $m);
        return $m[1] ?? '';
    }

    /**
     * save To File table data.
     *
     * @param \Vanguard\Models\Table\Table $table
     * @param string $filename
     * @param string $type
     * @return void
     */
    protected function saveTmpFile(Table $table, string $filename, string $type)
    {
        switch ($type) {
            case 'CSV': $dwn_mode = ExcelFormat::CSV; break;
            case 'XLSX':
            default: $dwn_mode = ExcelFormat::XLSX; break;
        }

        $data = $this->prepare_Excel($table);
        Excel::store($data, 'tmp/'.$filename, 'local', $dwn_mode);
    }

    /*
     * Prepare Excel writer class
     */
    protected function prepare_Excel(Table $table)
    {
        $data = [0 => []];
        foreach ($table->_fields as $val) {
            if (!$this->viewCols || in_array($val->field, $this->viewCols)) {
                $data[0][] = implode(' ', array_unique(explode(',', $val->name)));
            }
        }

        foreach ($this->backupRows as $row) {
            $row = (array)$row;
            $tmp_row = [];
            foreach ($table->_fields as $hdr) {
                if (!$this->viewCols || in_array($hdr->field, $this->viewCols)) {
                    $tmp_row[] = !empty($row[$hdr->field]) ? $row[$hdr->field] : '';
                }
            }
            $data[] = $tmp_row;
        }

        return new ArrayExport($data);
    }
}