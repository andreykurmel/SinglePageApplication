<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\TabldaFile;
use Vanguard\Models\DDLItem;
use Vanguard\Models\File;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class FileRepository
{
    /**
     * @var HelperService
     */
    protected $service;

    /**
     * @var string[]
     */
    public static $img_extensions = ['jpg', 'jpeg', 'gif', 'png', 'svg', 'webp', 'jfif', 'tiff', 'bmp'];

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
    public function storeTempFiles(Table $table_info, string $temp_hash, int $row_id)
    {
        $filePath = 'public/' . $this->getStorageTable($table_info) . '/';
        if (Storage::exists($filePath . $temp_hash . '/')) {
            $files = File::where('row_id', '=', $temp_hash)->get();
            foreach ($files as $file) {
                $file->row_id = $row_id;
                $file->filepath = preg_replace('/' . $temp_hash . '/', $row_id, $file->filepath);
                $this->storeFile($file->toArray());
            }

            Storage::move($filePath . $temp_hash . '/', $filePath . $row_id . '/');
        }
    }

    /**
     * Get Table Name in Storage Path.
     *
     * @param Table $table
     * @param bool $no_id
     * @return string
     */
    public function getStorageTable(Table $table, bool $no_id = false)
    {
        $part = $no_id ? '' : $table->id . '_';
        return $part . preg_replace('/[^\w\d]/i', '_', $table->name);
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
            File::where('id', '=', $data['id'])->update($newarr);
        } else {
            File::insert($newarr);
        }
        return File::where('filehash', '=', $newarr['filehash'])->first();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getFileVals(array $data): array
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
     * @param $filehash
     * @return mixed
     */
    public function getByHash($filehash)
    {
        return File::where('filehash', '=', $filehash)->first();
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @param int|array $row_ids
     * @return array
     */
    public function getEmailPaths(int $table_id, int $field_id, $row_ids): array
    {
        $row_ids = is_array($row_ids) ? $row_ids : [$row_ids];
        $files = $this->getPathsForRows($table_id, $field_id, $row_ids);
        $paths = [];
        foreach ($files as $file) {
            $paths[storage_path('app/public/') . $file->filepath . $file->filename] = $file->filename;
        }
        return $paths;
    }

    /**
     * @param int $table_id
     * @param int $header_id
     * @param $rows_ids
     * @return mixed
     */
    public function getPathsForRows(int $table_id, int $header_id, $rows_ids): Collection
    {
        $files = File::where('table_id', '=', $table_id)
            ->where('table_field_id', '=', $header_id)
            ->whereIn('row_id', $rows_ids)
            ->select(['id', 'row_id', 'filepath', 'filename'])
            ->get();

        foreach ($files as $file) {
            $file->fullpath = $file->filepath . $file->filename;
        }

        return $files;
    }

    /**
     * @param array $data
     * @param UploadedFile $upload_file
     * @return string|null
     * @throws \Exception
     */
    public function saveInTemp(array $data, UploadedFile $upload_file)
    {
        if (!$this->checkFile($data, $upload_file)) {
            return null;
        }

        $filename = $data['row_id'] ?? $upload_file->getClientOriginalName();
        if (!empty($data['file_link'])) {
            Storage::put('tmp_import/' . $data['table_field_id'] . '/' . $filename, file_get_contents($data['file_link']));
        } else {
            $upload_file->storeAs('tmp_import/' . $data['table_field_id'], $filename);
        }
        return $upload_file->getClientOriginalName();
    }

    /**
     * @param array $data
     * @param \Illuminate\Http\UploadedFile|\Vanguard\Classes\TabldaFile|null $upload_file
     * @return bool
     * @throws \Exception
     */
    protected function checkFile(array $data, $upload_file = null)
    {
        if (!empty($data['clear_before'])) {
            if ($data['table_id'] == 'temp') {
                Storage::delete('tmp_import/' . $data['table_field_id'] . '/' . $data['row_id']);
            } else {
                $fl = File::where('table_id', '=', $data['table_id'])
                    ->where('table_field_id', '=', $data['table_field_id'])
                    ->where('row_id', '=', $data['row_id'])
                    ->first();
                if ($fl) {
                    Storage::delete('public/' . $fl->filepath . $fl->filename);
                }
            }
            File::where('table_id', '=', $data['table_id'])
                ->where('table_field_id', '=', $data['table_field_id'])
                ->where('row_id', '=', $data['row_id'])
                ->delete();
        }
        if (!$upload_file && empty($data['file_link'])) {
            return false;
        }
        return true;
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
     * @param int $col_id
     * @param int $row_id
     * @param string $file_link
     * @return mixed|null
     */
    public function insertFileLink(int $table_id, int $col_id, int $row_id, string $file_link)
    {
        $file_data = [
            'table_id' => $table_id,
            'table_field_id' => $col_id,
            'row_id' => $row_id,
            'file_link' => $file_link,
        ];
        return $this->insertFile($file_data);
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
    public function insertFile(array $data, $upload_file = null)
    {
        if (!$this->checkFile($data, $upload_file)) {
            return null;
        }

        $this->fileAllowToFormat($data, $upload_file);

        $file = new File(array_merge([
            'table_id' => $data['table_id'],
            'table_field_id' => $data['table_field_id'],
            'row_id' => $data['row_id']
        ], $this->service->getModified(), $this->service->getCreated()));

        $filePath = $this->getStorageTable($file->_table_info) . '/';
        if ($file->row_id && $file->table_field_id) {
            $rid = preg_match('/^ddl/i', $file->row_id) ? 'ddl_item' : $file->row_id;
            $filePath .= $rid . '/' . $file->_table_field->field . '/';
        }

        $file_link_content = '';
        $flNewName = preg_replace('/[^\w\d\(\)\.]/i', '_', $data['file_new_name'] ?? '');
        $fname = pathinfo($flNewName, PATHINFO_FILENAME);
        $ext = pathinfo($flNewName, PATHINFO_EXTENSION);
        if (!empty($data['file_link'])) {
            $fileName = explode('/', preg_replace('/\?.*/i', '', $data['file_link']));
            $fileName = preg_replace('/[^\w\d\(\)\.]/i', '_', last($fileName));
            $fname = $fname ?: pathinfo($fileName, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileName, PATHINFO_EXTENSION);
            $file_link_content = file_get_contents($data['file_link']);
            Storage::put('public/' . $filePath . '/' . $fname . '.' . $ext, $file_link_content);
        } else {
            $fileName = preg_replace('/[^\w\d\(\)\.]/i', '_', $upload_file->getClientOriginalName());
            $fname = $fname ?: pathinfo($fileName, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileName, PATHINFO_EXTENSION);
            $upload_file->storeAs('public/' . $filePath, $fname . '.' . $ext);
        }

        (new TableDataRepository())->saveToCellLastFilePath($data, $filePath . $fileName);

        $file->filesize = $upload_file ? $upload_file->getSize() : strlen($file_link_content);
        $file->filepath = $filePath;
        $file->filename = $fname . '.' . $ext;
        $file->is_img = (in_array(strtolower($ext), self::$img_extensions) ? 1 : 0);
        $file = $this->storeFile($file->toArray());

        if ($data['table_id'] && floatval($data['row_id']) > 0 && is_numeric($data['row_id'])) {
            (new TableDataService())->rowInTableChanged($data['table_id'], $data['row_id']);
        }

        return $file;
    }

    /**
     * @param array $data
     * @param \Illuminate\Http\UploadedFile|\Vanguard\Classes\TabldaFile|null $upload_file
     * @throws \Exception
     */
    public function fileAllowToFormat(array $data, $file = null)
    {
        $field = (new TableFieldRepository())->getField($data['table_field_id']);
        if ($field->f_format) {
            $extension = pathinfo($file ? $file->getClientOriginalName() : $data['file_link'], PATHINFO_EXTENSION);
            $size = $file ? $file->getSize() : strlen(file_get_contents($data['file_link']));

            $format_ext = explode('-', $field->f_format)[0] ?? '';
            $format_size = intval(explode('-', $field->f_format)[1] ?? '');

            if ($format_ext && !in_array($extension, explode(',', $format_ext))) {
                throw new \Exception('The file format '.$extension.' is not allowed for uploading!', 1);
            }

            if ($format_size && $size > $format_size*1024*1024) {
                throw new \Exception('The size of the file exceeds the set limit '.$format_size.'Mb!', 1);
            }
        }
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
                $new_fl['id'] = null;
                $new_fl['row_id'] = $to_row_id;
                $new_fl['filepath'] = preg_replace('#/' . $from_row_id . '/#', '/' . $to_row_id . '/', $fl['filepath']);
                //copy file
                $this->storageCopy($fl, $new_fl);
                //copy record about file
                $this->storeFile($new_fl);
                //if last
                if ($i == count($files) - 1) {
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
        } catch (\Exception $e) {
        }
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
        if (($data['filename'] ?? '') != $fileObj->filename) {
            $flNewName = preg_replace('/[^\w\d\(\)\.]/i', '_', $data['filename']);
            $fname = pathinfo($flNewName, PATHINFO_FILENAME);
            $ext = pathinfo($flNewName, PATHINFO_EXTENSION);
            $fname = $fname ?: pathinfo($fileObj->filename, PATHINFO_FILENAME);
            $ext = $ext ?: pathinfo($fileObj->filename, PATHINFO_EXTENSION);

            $path = 'public/' . $fileObj->filepath . '/';
            Storage::move($path . $fileObj->filename, $path . $fname . '.' . $ext);
            $fileObj->update(array_merge(['filename' => $fname . '.' . $ext], $this->service->getModified()));
        }
        if ($data['notes'] ?? '') {
            $fileObj->update(array_merge(['notes' => $data['notes']], $this->service->getModified()));
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
    public function deleteFile($data)
    {
        $fileObj = File::where('id', '=', $data['id'])->first();
        if ($fileObj) {

            $res = File::where('id', '=', $fileObj->id)->delete();

            $path = 'public/' . $fileObj->filepath . '/';
            Storage::delete($path . $fileObj->filename);

            (new TableDataRepository())->saveToCellLastFilePath($fileObj->toArray(), '');

            return $res;
        }
        return false;
    }

    /**
     * Copy Attachments from Table to NewTable.
     *
     * @param Table $old_table
     * @param Table $new_table
     */
    public function copyTableAttachments(Table $old_table, Table $new_table)
    {
        $old_table->load([
            '_fields' => function ($q) {
                $q->with('_files');
            }
        ]);
        $new_table->load('_fields');

        $attachments = [];
        //copy files attached to Table
        foreach ($old_table->_attached_files as $attached_file) {
            $fl = array_merge($attached_file->toArray(), ['table_id' => $new_table->id]);
            $attachments[] = array_merge($this->service->delSystemFields($fl), $this->service->getModified(), $this->service->getCreated());
        }
        //copy files attached to Fields
        $old_folders = [];
        foreach ($old_table->_fields as $field) {
            $new_field = $new_table->_fields->where('field', $field->field)->first();
            if ($new_field) {
                foreach ($field->_files as $_file) {
                    $old_folders[] = storage_path('app/public/') . preg_replace('/\/.+/i', '', $_file->filepath);
                    $filepath = preg_replace('/^[^\/]+\//i', $this->getStorageTable($new_table) . '/', $_file->filepath);
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
        $new_path = storage_path('app/public/') . $this->getStorageTable($new_table);
        foreach (collect($old_folders)->unique() as $old_path) {
            \File::copyDirectory($old_path, $new_path);
        }
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
        return File::insert($newarrays);
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
    public function deleteAllAttachments(Table $table)
    {
        $path = storage_path('app/public/') . $this->getStorageTable($table);
        \File::deleteDirectory($path);
    }

    /**
     * Delete Directory with Attachments for selected Column of the Table.
     *
     * @param Table $table
     * @param string $field
     * @throws \Exception
     */
    public function deleteAttachmentsOfColumn(Table $table, string $field)
    {
        $column = $table->_fields()->where('field', '=', $field)->first();
        if ($column) {
            $files = File::where('table_id', '=', $table->id)
                ->where('table_field_id', '=', $column->id)
                ->get();

            $basepath = storage_path('app/public/') . $this->getStorageTable($table) . '/';
            $directories = collect([]);
            foreach ($files as $file) {
                $dir = $basepath . $file->row_id . '/' . $column->field . '/';
                $directories->push($dir);
            }
            foreach ($directories->unique() as $path) {
                \File::deleteDirectory($path);
            }

            File::where('table_id', '=', $table->id)
                ->where('table_field_id', '=', $column->id)
                ->delete();
        }
    }

    /**
     *
     */
    public function fixDDLfiles()
    {
        $files = File::where('row_id', 'like', 'ddl_%-%')->get();
        foreach ($files as $file) {
            $ddl_item = DDLItem::where('image_path', '=', $file->filepath . $file->filename)->first();
            if ($ddl_item) {
                $file->update(['row_id' => 'ddl_' . $ddl_item->ddl_id . '_row' . $ddl_item->id]);
            } else {
                $file->delete();
            }
        }
    }
}