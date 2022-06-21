<?php

namespace Vanguard\Modules\CloudBackup;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;

class CloudBackuper
{
    private $sender_strategy;
    private $backup;

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
                break;
            case 'Dropbox':
                $this->sender_strategy = new DropBoxStrategy($backup->_cloud);
                break;
            case 'OneDrive':
                $this->sender_strategy = new OneDriveStrategy($backup->_cloud);
                break;
            default:
                throw new \Exception('CloudBackuper:Undefined strategy type');
        }
        $this->backup = $backup;
    }

    /**
     * Send Table and Attachments to CloudStorage
     */
    public function sendToCloud()
    {
        $table_name = $this->backup->_table ? (new FileRepository())->getStorageTable($this->backup->_table, true) : '';
        $ymd = date('Ymd', time());
        $backup_name = !$this->backup->overwrite
            ? $table_name . '_' . $ymd
            : $table_name;

        if ($this->backup->mysql) {
            try {
                //create mysql dump
                $file_p =  storage_path('tmp/' . $backup_name . '.sql');
                exec('export MYSQL_PWD='.env('DB_PASSWORD').' ; '
                    . '/usr/bin/mysqldump -h '.env('DB_HOST').' -u root ' . env('DB_DATABASE_DATA') . ' '
                    . $this->backup->_table->db_name . ' --single-transaction --quick > ' . $file_p);
                //upload mysql dump
                $target_p = '/' . $this->backup->getsubfolder() . '/' . $table_name . '/' . $backup_name . '.sql';
                $this->sender_strategy->upload($file_p, $target_p);
                //remove mysql dump
                exec('rm /var/www/' . $backup_name . '.sql');
            } catch (\Exception $e) {
                Log::channel('jobs')->info('Backup error: "mysql - '.$backup_name.' (bkp id '.$this->backup->id.') - "'.$e->getMessage());
            }
        }

        if ($this->backup->csv) {
            try {
                //create csv
                $this->saveTmpFile($this->backup->_table, $backup_name, 'CSV');
                //upload csv
                $target_p = '/'.$this->backup->getsubfolder() . '/' . $table_name . '/' . $backup_name . '.csv';
                $this->sender_strategy->upload(storage_path('tmp/'.$backup_name.'.csv'), $target_p);
                //remove csv
                exec('rm ' . storage_path('tmp/'.$backup_name.'.csv'));
            } catch (\Exception $e) {
                Log::channel('jobs')->info('Backup error: "csv - '.$backup_name.' (bkp id '.$this->backup->id.') - "'.$e->getMessage());
            }
        }

        if ($this->backup->attach) {
            $prefx = !$this->backup->overwrite ? $ymd.'_' : '';
            //save attachments
            $files = File::where('table_id', '=', $this->backup->_table->id)->get() ?: collect([]);
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

                        $target_p = '/' . $this->backup->getsubfolder() . '/';
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
    private function saveTmpFile(Table $table, string $filename, string $type)
    {
        $res = "";
        $data = [
            'table_id' => $table->id,
            'page' => 1,
            'rows_per_page' => 0,
            'force_execute' => true,
        ];
        switch ($type) {
            case 'CSV':
                $dwn_mode = "csv";
                $writer = $this->prepare_Excel($table, $data, $filename, true);
                $writer->store('csv', storage_path('tmp'));
                break;
            case 'XLSX':
                $dwn_mode = "xlsx";
                $writer = $this->prepare_Excel($table, $data, $filename, true);
                $writer->store('xlsx', storage_path('tmp'));
                break;
            default:
                $dwn_mode = "";
                break;
        }
    }

    /*
     * Prepare Excel writer class
     */
    private function prepare_Excel(Table $table, $post, $filename, $ignore_is_showed = false)
    {
        $tableRows = (new TableDataRepository())->getRows($post, $table->user_id);

        $data = [0 => []];
        foreach ($table->_fields as $val) {
            $data[0][] = implode(' ', array_unique(explode(',', $val->name)));
        }

        foreach ($tableRows['rows'] as $row) {
            $row = (array)$row;
            $tmp_row = [];
            foreach ($table->_fields as $hdr) {
                $tmp_row[] = !empty($row[$hdr->field]) ? $row[$hdr->field] : '';
            }
            $data[] = $tmp_row;
        }

        return \Maatwebsite\Excel\Facades\Excel::create($filename, function ($excel) use ($data) {
            $excel->sheet('Sheet1', function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', true, false);
            });
        });
    }
}