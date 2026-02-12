<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\File;
use Vanguard\Models\RemoteFile;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Modules\RemoteFilesModule;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class RemoteFilesRepository
{
    /**
     * @var HelperService
     */
    protected $service;

    /**
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param int $file_id
     * @return RemoteFile|null
     */
    public function get(int $file_id)
    {
        return RemoteFile::where('id', '=', $file_id)->first();
    }

    /**
     * @param $filehash
     * @return mixed
     */
    public function getByHash($filehash)
    {
        return RemoteFile::where('filehash', '=', $filehash)->first();
    }

    /**
     * @param array $main
     * @param string $link
     * @param string $filename
     * @param string $cloud_meta
     * @param bool $can_upload
     * @return RemoteFile
     * @throws Exception
     */
    public function insert(array $main, string $link, string $filename = '', string $cloud_meta = '', bool $can_upload = false): RemoteFile
    {
        $filename = $filename ?: $link;
        $fname = pathinfo($filename, PATHINFO_FILENAME);

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $is_img = in_array(strtolower($ext), FileRepository::$img_or_video_extensions);
        $is_video = in_array(strtolower($ext), FileRepository::$video_extensions);
        $is_audio = in_array(strtolower($ext), FileRepository::$audio_extensions);

        $special_mark = null;
        $special_content = null;
        if (preg_match('/www.youtube.com\/embed\/.+/i', $link)) {
            $special_mark = 'youtube';
            $is_img = $is_video = true;
        }
        if (preg_match('/player.vimeo.com\/video\/.+/i', $link)) {
            $special_mark = 'vimeo';
            $is_img = $is_video = true;
            $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$fname.php"));
            $special_content = Arr::first($hash)['thumbnail_medium'] ?? '';
        }

        return RemoteFile::create(
            array_merge([
                'table_id' => $main['table_id'],
                'table_field_id' => $main['table_field_id'],
                'row_id' => $main['row_id'],
                'remote_link' => $link,
                'filename' => $fname . ($ext ? '.'.$ext : ''),
                'special_mark' => $special_mark,
                'special_content' => $special_content,
                'cloud_meta' => $cloud_meta,
                'filehash' => Uuid::uuid4(),
                'can_upload' => $can_upload ? 1 : 0,
                'is_img' => $is_img ? 1 : 0,
                'is_video' => $is_video ? 1 : 0,
                'is_audio' => $is_audio ? 1 : 0,
            ],
                $this->service->getModified(),
                $this->service->getCreated())
        );
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     * @return bool
     * @throws Exception
     */
    public function delete(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null, bool $with_cloud = false): bool
    {
        //remove from storage
        $this->deleteStoredRemotes($table_id, $field_id, $row_id, $direct_id);

        if ($direct_id && $with_cloud) {
            $this->removeFromCloud($direct_id);
        }

        //remove from database
        return $this->getSql($table_id, $field_id, $row_id, $direct_id)->delete();
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     * @return Builder
     */
    public function getSql(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null): Builder
    {
        $sql = RemoteFile::query()->where('table_id', '=', $table_id);
        if ($field_id) {
            $sql->where('table_field_id', '=', $field_id);
        }
        if ($row_id) {
            $sql->where('row_id', '=', $row_id);
        }
        if ($direct_id) {
            $sql->where('id', '=', $direct_id);
        }
        return $sql;
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     */
    protected function deleteStoredRemotes(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null): void
    {
        if ($field_id || $row_id || $direct_id) {
            $sql = $this->getSql($table_id, $field_id, $row_id, $direct_id);
            $sql->chunk(200, function ($files) {
                foreach($files as $file) {
                    Storage::delete("remote_image_thumbs/{$file->table_id}/{$file->filehash}");
                }
            });
        } else {
            $path = storage_path("remote_image_thumbs/{$table_id}");
            \File::deleteDirectory($path);
        }
    }

    /**
     * @param int $id
     * @param string|null $notes
     * @return bool
     */
    public function update(int $id, string $notes = null): bool
    {
        return RemoteFile::where('id', '=', $id)->update([
            'notes' => $notes,
        ]);
    }

    /**
     * @param int $id
     * @param string $local_thumb
     * @return bool
     */
    public function setThumb(int $id, string $local_thumb): bool
    {
        return RemoteFile::where('id', '=', $id)->update([
            'local_thumb' => $local_thumb,
        ]);
    }

    /**
     * @param Table $table_from
     * @param Table $table_to
     * @param array $rows_correspondence : [from_id => to_id]
     * @param array $fields_correspondence : [from_id => to_id]
     */
    public function copyAttachForRows(Table $table_from, Table $table_to, array $rows_correspondence, array $fields_correspondence = []): void
    {
        $files = RemoteFile::where('table_id', '=', $table_from->id)
            ->whereIn('row_id', array_keys($rows_correspondence))
            ->get();

        foreach ($rows_correspondence as $from_row_id => $to_row_id) {
            $filesPart = $files->where('row_id', '=', $from_row_id);
            foreach ($filesPart as $file) {
                if (!$fields_correspondence || !empty($fields_correspondence[$file->table_field_id])) {
                    RemoteFile::create(
                        array_merge([
                            'table_id' => $table_to->id,
                            'table_field_id' => $fields_correspondence[$file->table_field_id] ?? $file->table_field_id,
                            'row_id' => $to_row_id,
                            'remote_link' => $file->remote_link,
                            'filename' => $file->filename,
                            'special_mark' => $file->special_mark,
                            'special_content' => $file->special_content,
                            'is_img' => $file->is_img,
                            'is_video' => $file->is_video,
                            'is_audio' => $file->is_audio,
                        ],
                            $this->service->getModified(),
                            $this->service->getCreated()
                        ));
                }
            }
        }
    }

    /**
     * @param File $file
     * @return string
     */
    public function hasConnectedCloud(File $file): string
    {
        $field = $this->fieldsForRemote($file->table_id, $file->table_field_id)->first();
        if ($field && $field->fetch_uploading && is_numeric($file->row_id)) {
            $row = (new TableDataRepository())->getDirectRow($file->_table_info, $file->row_id) ?? [];
            $newlink = $row[$field->_fetch_field->field] ?? '';
            return $newlink;
        }
        return '';
    }

    /**
     * @return Collection|TableField[]
     */
    public function fieldsForRemote(int $table_id, int $field_id = null): Collection
    {
        $sql = TableField::where('table_id', '=', $table_id)
            ->where('f_type', '=', 'Attachment')
            ->where('input_type', '=', 'Fetch')
            ->whereHas('_fetch_field')
            ->with(['_fetch_field', '_fetch_cloud_field']);
        if ($field_id) {
            $sql->where('id', '=', $field_id);
        }
        return $sql->get();
    }

    /**
     * @param File $file
     * @param string $shared_link
     * @param string $content_data
     * @return RemoteFile|null
     */
    public function uploadToFirstCloud(File $file, string $shared_link, string $content_data)
    {
        $user = auth()->user() ?: new User();
        $remoteModule = new RemoteFilesModule($file->table_id, $user);
        $field = $this->fieldsForRemote($file->table_id, $file->table_field_id)->first();
        return $remoteModule->fileToSharedFolder($field, $file, $shared_link, $content_data);
    }

    /**
     * @param int $remote_file_id
     */
    public function removeFromCloud(int $remote_file_id)
    {
        $user = auth()->user() ?: new User();
        $file = $this->get($remote_file_id);
        $remoteModule = new RemoteFilesModule($file->table_id, $user);
        $field = $this->fieldsForRemote($file->table_id, $file->table_field_id)->first();
        $remoteModule->removeFromSharedFolder($field, $file);
    }
}