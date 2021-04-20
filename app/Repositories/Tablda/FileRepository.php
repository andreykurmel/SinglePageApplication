<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\TabldaFile;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class FileRepository
{
    protected $service;

    /**
     * FileRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Save files from tmp folder to row's folder.
     *
     * @param Table $table_info
     * @param string $temp_hash
     * @param int $row_id
     */
    public function saveTempFiles(Table $table_info, string $temp_hash, int $row_id) {
        $filePath = 'public/' . $this->getStorageTable($table_info) . '/';
        if (Storage::exists($filePath.$temp_hash.'/'))
        {
            $files = File::where('row_id', $temp_hash)->get();
            foreach ($files as $file) {
                $file->row_id = $row_id;
                $file->filepath = preg_replace('/'.$temp_hash.'/', $row_id, $file->filepath);
                $this->storeFile($file->toArray());
            }

            Storage::move($filePath.$temp_hash.'/', $filePath.$row_id.'/');
        }
    }

    /**
     * @param $filehash
     * @return mixed
     */
    public function getByHash($filehash)
    {
        return File::where('filehash', '=', $filehash)->first();
    }

    /**
     * Insert file into the user`s table row.
     *
     * @param array $data - example:
     * [
     *  +table_id: 2,
     *  +table_field_id: 47,
     *  +row_id: 15,
     *  -file_link: 'http://some_url/file.jpg'
     *  -clear_before: false
     * ]
     * @param \Illuminate\Http\UploadedFile|\Vanguard\Classes\TabldaFile|null $upload_file - //file data from request//
     * @return mixed
     */
    public function insertFile($data, $upload_file) {
        if (!empty($data['clear_before'])) {
            File::where('table_id', '=', $data['table_id'])
                ->where('table_field_id', '=', $data['table_field_id'])
                ->where('row_id', '=', $data['row_id'])
                ->delete();
        }

        $file = new File( array_merge([
            'table_id' => $data['table_id'],
            'table_field_id' => $data['table_field_id'],
            'row_id' => $data['row_id']
        ], $this->service->getModified(), $this->service->getCreated()) );

        $filePath = $this->getStorageTable($file->_table_info) . '/';
        if ($file->row_id && $file->table_field_id) {
            $rid = preg_match('/^ddl/i', $file->row_id) ? 'ddl_item' : $file->row_id;
            $filePath .= $rid.'/'.$file->_table_field->field.'/';
        }

        $flNewName = preg_replace('/[^\w\d\(\)\.]/i', '_', $data['file_new_name'] ?? '');
        $fname = pathinfo($flNewName, PATHINFO_FILENAME);
        $ext = pathinfo($flNewName, PATHINFO_EXTENSION);
        if (!empty($data['file_link'])) {
            $fileName = explode('/', $data['file_link']);
            $fileName = preg_replace('/[^\w\d\(\)\.]/i', '_', last($fileName));
            $fname = $fname ?: pathinfo($fileName, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileName, PATHINFO_EXTENSION);
            Storage::put('public/'.$filePath.'/'.$fname.'.'.$ext, file_get_contents($data['file_link']));
        } else {
            $fileName = preg_replace('/[^\w\d\(\)\.]/i', '_', $upload_file->getClientOriginalName());
            $fname = $fname ?: pathinfo($fileName, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileName, PATHINFO_EXTENSION);
            $upload_file->storeAs('public/'.$filePath, $fname.'.'.$ext);
        }

        (new TableDataRepository())->saveToCellLastFilePath($data, $filePath.$fileName);

        $file->filepath = $filePath;
        $file->filename = $fname.'.'.$ext;
        $file->is_img = (in_array( strtolower($ext), ['jpg', 'jpeg', 'gif', 'png']) ? 1 : 0);
        $file = $this->storeFile($file->toArray());

        if ($data['table_id'] && floatval($data['row_id']) > 0 && is_numeric($data['row_id'])) {
            (new TableDataService())->rowInTableChanged($data['table_id'], $data['row_id']);
        }

        return $file;
    }

    /**
     * @param int $table_id
     * @param int $col_id
     * @param int $row_id
     * @param string $filename
     * @param string $content
     * @return mixed
     */
    public function insertFileAlias(int $table_id, int $col_id, int $row_id, string $filename, string $content)
    {
        $json_file = new TabldaFile($filename, $content);
        $file_data = [
            'table_id' => $table_id,
            'table_field_id' => $col_id,
            'row_id' => $row_id,
            'clear_before' => true,
        ];
        return $this->insertFile($file_data, $json_file);
    }

    /**
     * @param int $table_id
     * @param array $rows_correspondence : [from_id => to_id]
     */
    public function copyAttachForRows(int $table_id, array $rows_correspondence)
    {
        $dataRepo = new TableDataRepository();

        foreach ($rows_correspondence as $from_row_id => $to_row_id) {

            $files = File::where('table_id', '=', $table_id)
                ->where('row_id', '=', $from_row_id)
                ->get()
                ->toArray();

            foreach ($files as $i => $fl) {
                $new_fl = $fl;
                $new_fl['row_id'] = $to_row_id;
                $new_fl['filepath'] = preg_replace('#/'.$from_row_id.'/#','/'.$to_row_id.'/', $fl['filepath']);
                //copy file
                $this->storageCopy($fl, $new_fl);
                //copy record about file
                $this->storeFile($new_fl);
                //if last
                if ($i == count($files)-1) {
                    $dataRepo->saveToCellLastFilePath($new_fl, $new_fl['filepath'] . $new_fl['filename']);
                }
            }
        }
    }

    /**
     * @param array $from
     * @param array $to
     */
    public function storageCopy(array $from, array $to)
    {
        try {
            Storage::copy(
                'public/' . $from['filepath'] . $from['filename'],
                'public/' . $to['filepath'] . $to['filename']
            );
        }
        catch (\Exception $e) {}
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getFileVals(array $data) : array
    {
        $newarr = array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified(),
            $this->service->getCreated()
        );
        $newarr['filehash'] = Uuid::uuid4();
        return $newarr;
    }

    /**
     * Store file record.
     *
     * @param array $data
     * @return mixed
     */
    protected function storeFile(array $data)
    {
        $newarr = $this->getFileVals($data);
        if ($data['id'] ?? '') {
            File::where('id', '=', $data['id'])->update( $newarr );
        } else {
            File::insert( $newarr );
        }
        return File::where('filehash', '=', $newarr['filehash'])->first();
    }

    /**
     * Store array of files records.
     *
     * @param array $datas
     * @return mixed
     */
    protected function massStoreFiles(array $datas)
    {
        $newarrays = [];
        foreach ($datas as $dat) {
            $newarrays[] = $this->getFileVals($dat);
        }
        return File::insert( $newarrays );
    }

    /**
     * Update file in the user`s table row.
     *
     * @param $data - array, example:
     * [
     *  id: 15,
     *  filename: 'some text'
     *  notes: 'some text'
     * ]
     * @return mixed
     */
    public function updateFile($data)
    {
        $fileObj = File::where('id', '=', $data['id'])->first();
        if ($data['filename'] ?? '') {
            $flNewName = preg_replace('/[^\w\d\(\)\.]/i', '_', $data['filename']);
            $fname = pathinfo($flNewName, PATHINFO_FILENAME);
            $ext = pathinfo($flNewName, PATHINFO_EXTENSION);
            $fname = $fname ?: pathinfo($fileObj->filename, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileObj->filename, PATHINFO_EXTENSION);

            $path = 'public/' . $fileObj->filepath . '/';
            Storage::move($path.$fileObj->filename, $path.$fname.'.'.$ext);
            $fileObj->update( array_merge(['filename' => $fname.'.'.$ext], $this->service->getModified()) );
        }
        if ($data['notes'] ?? '') {
            $fileObj->update( array_merge(['notes' => $data['notes']], $this->service->getModified()) );
        }
        return $fileObj;

    }

    /**
     * Delete file from user`s table row.
     *
     * @param $data - array, example:
     * [
     *  id: 15,
     *  table_id: 2,
     *  table_field_id: 47
     * ]
     * @return mixed
     */
    public function deleteFile($data) {
        $res = File::where('id', '=', $data['id'])
            ->delete();

        (new TableDataRepository())->saveToCellLastFilePath($data, '');

        return $res;
    }

    /**
     * Copy Attachments from Table to NewTable.
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyTableAttachments(Table $old_table, Table $new_table) {
        $old_table->load([
            '_fields' => function ($q) { $q->with('_files'); }
        ]);
        $new_table->load('_fields');

        $attachments = [];
        //copy files attached to Table
        foreach ($old_table->_attached_files as $attached_file) {
            $fl = array_merge($attached_file->toArray(), ['table_id' => $new_table->id]);
            $attachments[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
        }
        //copy files attached to Fields
        foreach ($old_table->_fields as $field) {
            $new_field = $new_table->_fields->where('field', $field->field)->first();
            if ($new_field) {
                foreach ($field->_files as $_file) {
                    $filepath = preg_replace('/^[^\/]+\//i', $this->getStorageTable($new_table).'/', $_file->filepath);
                    $attachments[] = array_merge($_file->toArray(), [
                        'table_id' => $new_table->id,
                        'table_field_id' => $new_field->id,
                        'filepath' => $filepath,
                    ]);
                }
            }
        }
        $this->massStoreFiles($attachments);

        //copy files in Storage
        $old_path = storage_path('app/public/') . $this->getStorageTable($old_table);
        $new_path = storage_path('app/public/') . $this->getStorageTable($new_table);
        \File::copyDirectory($old_path, $new_path);
    }

    /**
     * @param Table $table
     * @param array $row_ids
     */
    public function deleteFilesForRow(Table $table, array $row_ids)
    {
        foreach ($row_ids as $rid) {
            $path = storage_path('app/public/') . $this->getStorageTable($table) . '/' . $rid;
            \File::deleteDirectory($path);
        }
    }

    /**
     * Delete Directory with Attachments for selected Table.
     *
     * @param Table $table
     */
    public function deleteAttachments(Table $table) {
        $path = storage_path('app/public/') . $this->getStorageTable($table);
        \File::deleteDirectory($path);
    }

    /**
     * Get Table Name in Storage Path.
     *
     * @param Table $table
     * @return string
     */
    public function getStorageTable(Table $table) {
        return $table->id
            . '_'
            . preg_replace('/[^\w\d]/i', '_', $table->name);
    }
}