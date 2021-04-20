<?php

namespace Vanguard\Modules\CloudBackup;


use Maatwebsite\Excel\Excel;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

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
        $table_name = $this->backup->_table ? (new FileRepository())->getStorageTable($this->backup->_table) : '';
        $backup_name = $table_name.'_' . date('Ymd', time());

        if ($this->backup->mysql) {
            //create mysql dump
            exec('/usr/bin/mysqldump -u '
                . env('DUMP_ALL_DB_USER') . ' ' . env('DB_DATABASE_DATA') . ' '
                . $this->backup->_table->db_name . ' --single-transaction --quick > /var/www/' . $backup_name . '.sql');
            //upload mysql dump
            $target_p = $this->backup->_cloud->root_folder . '/' . $table_name;
            $this->sender_strategy->upload('/var/www/', $backup_name . '.sql', $target_p);
            //remove mysql dump
            exec('rm /var/www/' . $backup_name . '.sql');
        }

        if ($this->backup->csv) {
            //create csv
            $this->saveTmpFile($this->backup->_table, $backup_name, 'CSV');
            //upload csv
            $target_p = $this->backup->_cloud->root_folder . '/' . $table_name;
            $this->sender_strategy->upload(storage_path('tmp'), $backup_name . '.csv', $target_p);
            //remove csv
            exec('rm ' . storage_path('tmp') . '/' . $backup_name . '.csv');
        }

        if ($this->backup->attach) {
            //save attachments
            $files = File::where('table_id', '=', $this->backup->_table->id)->get();
            foreach ($files as $file) {
                //upload attachment
                $storage_path = storage_path('app/public/' . $file->filepath);
                $target_p = $this->backup->_cloud->root_folder . '/' . $file->filepath;
                $this->sender_strategy->upload($storage_path, $file->filename, $target_p);
            }
        }
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
            'rows_per_page' => 0
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
        $tableRows = (new TableDataRepository())->getRows($post, auth()->id());

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