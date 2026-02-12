<?php

namespace Vanguard\Modules;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Vanguard\Models\File;
use Vanguard\Models\RemoteFile;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserCloud;
use Vanguard\Modules\CloudBackup\DropBoxApiModule;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Modules\CloudBackup\OneDriveApiModule;
use Vanguard\Repositories\Tablda\RemoteFilesRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Support\FileHelper;
use Vanguard\User;

class RemoteFilesModule
{
    /**
     * @var User
     */
    protected $user;
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var RemoteFilesRepository
     */
    protected $remoteFiles;
    /**
     * @var UserCloudRepository
     */
    protected $cloudRepo;
    /**
     * @var TableDataService
     */
    protected $dataService;
    /**
     * @var bool
     */
    protected $forceThumbExt;

    /**
     *
     */
    public function __construct(int $table_id, User $user = null, string $forceThumbExt = '')
    {
        $this->table = (new TableRepository())->getTable($table_id);
        $this->remoteFiles = new RemoteFilesRepository();
        $this->cloudRepo = new UserCloudRepository();
        $this->dataService = new TableDataService();
        $this->user = $user ?: $this->table->_user;
        $this->forceThumbExt = $forceThumbExt;
    }

    /**
     * @return int
     */
    public function hasRemotes(): int
    {
        return $this->remoteFiles->fieldsForRemote($this->table->id)->count();
    }

    /**
     * @param int|null $field_id
     * @throws Exception
     */
    public function fill(int $field_id = null)
    {
        $this->clear($field_id);
        foreach ($this->remoteFiles->fieldsForRemote($this->table->id, $field_id) as $field) {
            $sql = new TableDataQuery($this->table);
            $this->chunkedFill($sql, $field);
        }
    }

    /**
     * @param int|null $field_id
     * @param int|null $row_id
     * @throws Exception
     */
    public function clear(int $field_id = null, int $row_id = null)
    {
        $this->remoteFiles->delete($this->table->id, $field_id, $row_id);
    }

    /**
     * @param TableDataQuery $sql
     * @param TableField $field
     * @throws Exception
     */
    protected function chunkedFill(TableDataQuery $sql, TableField $field)
    {
        $lines = $sql->getQuery()->count();
        $chunk = 100;

        for ($cur = 0; ($cur * $chunk) < $lines; $cur++) {

            $all_rows = $sql->getQuery()
                ->offset($cur * $chunk)
                ->limit($chunk)
                ->get()
                ->toArray();

            foreach ($all_rows as $row) {
                $this->createRemotes($field, $row);
            }
        }

        $this->dataService->newTableVersion($field->_table);
    }

    /**
     * @param TableField $field
     * @param array $newrow
     */
    protected function createRemotes(TableField $field, array $newrow)
    {
        $newlink = $newrow[$field->_fetch_field->field] ?? '';
        if ($newlink) {
            $this->remoteFilesCreation($field, $newrow['id'], $newlink);
        }
    }

    /**
     * @param TableField $field
     * @param int $row_id
     * @param string $newlink
     */
    public function remoteFilesCreation(TableField $field, int $row_id, string $newlink)
    {
        $main = [
            'table_id' => $this->table->id,
            'table_field_id' => $field->id,
            'row_id' => $row_id,
        ];
        $mch = [];

        //Youtube parser
        if (preg_match('/youtube.com\/watch\?v=(.+)/i', $newlink, $mch))
        {
            $fileid = $mch[1];
            $embedlink = 'https://www.youtube.com/embed/' . $fileid;
            $this->remoteFiles->insert($main, $embedlink);
        }
        //Vimeo parser
        elseif (preg_match('/vimeo.com\/(.+)/i', $newlink, $mch))
        {
            $fileid = $mch[1];
            $embedlink = 'https://player.vimeo.com/video/' . $fileid;
            $this->remoteFiles->insert($main, $embedlink);
        }
        //Google Drive Folder parser
        elseif (preg_match('/drive.google.com\/drive\/folders\/([^\/?]+)/i', $newlink, $mch))
        {
            [$cloud, $files] = $this->cloudAndFiles('Google', $field, $row_id, $newlink);
            $can_upload = $cloud && $this->googleApi()->canEditFolder($cloud->gettoken(), $mch[1]);
            foreach ($files as $file) {
                $link = $this->googleApi()->fileLink($file['id']);
                $remote_file = $this->remoteFiles->insert($main, $link, $file['name'], 'Google:'.$file['id'], $can_upload);
                $this->storeCloudThumbnail($cloud, $remote_file, $file['id']);
            }
        }
        //Dropbox Folder parser
        elseif (preg_match('/dropbox.com\/sh\//i', $newlink, $mch)
                || preg_match('/dropbox.com\/scl\/fo\//i', $newlink, $mch))
        {
            [$cloud, $files] = $this->cloudAndFiles('Dropbox', $field, $row_id, $newlink);
            $folder_path = Arr::first($files)['path_display'] ?? '';
            $can_upload = $cloud && $folder_path && $this->dropboxApi($cloud->cloud)->canEditFolder($cloud->id, $folder_path.'/');
            foreach ($files as $file) {
                if ($file && $file['.tag'] == 'file') {
                    $link = $this->dropboxApi($cloud->cloud)->fileLink($cloud->id, $file['id']);
                    $remote_file = $this->remoteFiles->insert($main, $link, $file['name'] ?? '', 'Dropbox:'.$file['id'], $can_upload);
                    $this->storeCloudThumbnail($cloud, $remote_file, $file['id']);
                }
            }
        }
        //OneDrive Folder or File parser
        elseif (preg_match('/1drv.ms\/u/i', $newlink, $mch))
        {
            [$cloud, $files] = $this->cloudAndFiles('OneDrive', $field, $row_id, $newlink);
            $can_upload = $cloud && $this->oneDriveApi()->canEditFolder($cloud->id, $newlink);
            foreach ($files as $file) {
                $link = $this->oneDriveApi()->fileLink($cloud->id, $file);
                $remote_file = $this->remoteFiles->insert($main, $link, $file['name'] ?? '', 'OneDrive:'.$file['id'], $can_upload);
                $this->storeCloudThumbnail($cloud, $remote_file, $file['id']);
            }
        }
        //Google Drive File parser
        elseif (preg_match('/drive.google.com\/file\/d\/([^\/?]+)/i', $newlink, $mch)
                || preg_match('/docs.google.com\/document\/d\/([^\/?]+)/i', $newlink, $mch))
        {
            $fileid = $mch[1];
            $link = $this->googleApi()->fileLink($fileid);
            $cloud = $this->getCloud('Google', $field, $row_id);
            $driveFile = $cloud ? $this->googleApi()->fileMetadata($cloud->gettoken(), $fileid) : null;
            $cldmeta = $driveFile ? 'Google:'.$driveFile->id : '';
            $remote_file = $this->remoteFiles->insert($main, $link, $driveFile ? $driveFile->name : '', $cldmeta);
            $this->storeCloudThumbnail($cloud, $remote_file, $fileid);
        }
        //Dropbox File parser
        elseif (preg_match('/dropbox.com\/scl\/fi\/[^\/]+\/([^?]+)/i', $newlink, $mch)
                || preg_match('/dropbox.com\/s\/[^\/]+\/([^?]+)/i', $newlink, $mch))
        {
            $cloud = $this->getCloud('Dropbox', $field, $row_id);
            $dropFile = $this->dropboxApi($cloud->cloud)->sharedLinkMetadata($cloud->id, $newlink);
            $link = $this->dropboxApi($cloud->cloud)->fileLink($cloud->id, $dropFile['id']);
            $remote_file = $this->remoteFiles->insert($main, $link, $dropFile['name'] ?? '', 'Dropbox:'.$dropFile['id']);
            $this->storeCloudThumbnail($cloud, $remote_file, $dropFile['id']);
        }
        //Just link
        else
        {
            $this->remoteFiles->insert($main, $newlink);
        }
    }

    /**
     * @param UserCloud $cloud
     * @param RemoteFile $remote_file
     * @param string $file_id
     * @param string $file_content
     * @return string
     */
    protected function storeCloudThumbnail(UserCloud $cloud, RemoteFile $remote_file, string $file_id = '', string $file_content = ''): string
    {
        $ext = FileHelper::extension($remote_file->filename) ?: $this->forceThumbExt;
        if (
            ($this->forceThumbExt && $this->forceThumbExt != $ext)
            ||
            (!$this->forceThumbExt && (!$remote_file->is_img || $remote_file->is_video || $remote_file->is_audio))
        ) {
            return '';//applied just to images.
        }
        switch ($ext) {//https://developers.google.com/drive/api/guides/ref-export-formats
            case 'txt': $expMime = 'text/plain'; break;
            case 'docx': $expMime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break;
            default: $expMime = ''; break;
        }

        $store_path = "remote_image_thumbs/{$this->table->id}/{$remote_file->filehash}";
        if ($file_content) {
            Storage::put($store_path, $file_content);
            exec('chmod 666 ' . storage_path('app/'.$store_path));
        } else {
            switch ($cloud->cloud) {
                case 'Google':
                    $this->googleApi()->storeGoogleFile($cloud->gettoken(), $file_id, $store_path, $expMime);
                    break;
                case 'Dropbox':
                    $this->dropboxApi($cloud->cloud)->storeDropboxFile($cloud->id, $file_id, $store_path);
                    break;
                case 'OneDrive':
                    $this->oneDriveApi()->storeOneDriveFile($cloud->id, $file_id, $store_path);
                    break;
            }
        }

        $local_thumb = "{$this->table->id}/{$remote_file->filehash}";
        $this->remoteFiles->setThumb($remote_file->id, $local_thumb);
        return $local_thumb;
    }

    /**
     * @param RemoteFile $file
     * @return string
     */
    public function thumbPath(RemoteFile $file): string
    {
        $path = $file->local_thumb ?: "{$this->table->id}/{$file->filehash}";
        return storage_path("app/remote_image_thumbs/{$path}");
    }

    /**
     * @param TableField $field
     * @param File $file
     * @param string $shared_link
     * @param string $content_data
     * @return RemoteFile|null
     */
    public function fileToSharedFolder(TableField $field, File $file, string $shared_link, string $content_data)
    {
        $mch = [];
        $remot_link = '';
        $cloud_meta = '';
        //Google Drive Uploading
        if (preg_match('/drive.google.com\/drive\/folders\/([^\/?]+)/i', $shared_link, $mch)) {
            $folderid = $mch[1];
            $cloud = $this->getCloud('Google', $field, $file->row_id);
            $drive_file = $this->googleApi()->uploadToFolder($cloud->gettoken(), $folderid, $file->filename, $content_data);
            $remot_link = $drive_file
                ? $this->googleApi()->fileLink($drive_file['id'])
                : '';
            $cloud_meta = $drive_file ? 'Google:'.$drive_file['id'] : '';
        } //Dropbox Uploading
        elseif (preg_match('/dropbox.com\/s/i', $shared_link, $mch)) {//works for dropbox.com/scl/...
            [$cloud, $files] = $this->cloudAndFiles('Dropbox', $field, $file->row_id, $shared_link);
            $folder_path = Arr::first($files)['path_display'] ?? '';
            $folder_path = $folder_path
                ? pathinfo($folder_path, PATHINFO_DIRNAME) . '/'
                : '';
            $dbox_file = $folder_path && $cloud
                ? $this->dropboxApi($cloud->cloud)->uploadFile($cloud->id, $folder_path.$file->filename, $content_data)
                : [];
            $remot_link = $dbox_file && $cloud
                ? $this->dropboxApi($cloud->cloud)->fileLink($cloud->id, $dbox_file['id'])
                : '';
            $cloud_meta = $dbox_file ? 'Dropbox:'.$dbox_file['id'] : '';
        } //OneDrive Uploading
        elseif (preg_match('/1drv.ms\/u/i', $shared_link, $mch)) {
            $cloud = $this->getCloud('OneDrive', $field, $file->row_id);
            $one_file = $this->oneDriveApi()->uploadToOwnedParent($cloud->id, $shared_link, $file->filename, $content_data);
            $cloud_meta = $one_file ? 'OneDrive:'.$one_file['id'] : '';
        }

        $remote_file = null;
        if ($remot_link) {
            $remote_file = $this->remoteFiles->insert($file->toArray(), $remot_link, $file->filename, $cloud_meta, true);
            $remote_file->local_thumb = $this->storeCloudThumbnail(new UserCloud(), $remote_file, '', $content_data);
            $remote_file->is_remote = 1;
        }
        return $remote_file;
    }

    /**
     * @param TableField $field
     * @param RemoteFile $file
     */
    public function removeFromSharedFolder(TableField $field, RemoteFile $file): void
    {
        $meta = explode(':', $file->cloud_meta);
        if ($meta && count($meta) == 2) {
            $type = $meta[0];
            $cloud_file_id = $meta[1];

            $cloud = $this->getCloud($type, $field, $file->row_id);
            switch ($type) {
                case 'Google': $this->googleApi()->removeFile($cloud->gettoken(), $cloud_file_id); break;
                case 'Dropbox': $this->dropboxApi($cloud->cloud)->removeFile($cloud->id, $cloud_file_id); break;
                case 'OneDrive': $this->oneDriveApi()->removeFile($cloud->id, $cloud_file_id); break;
            }
        }
    }

    /**
     * @param string $type
     * @param TableField $field
     * @param int $row_id
     * @param string $folderlink
     * @return array
     */
    protected function cloudAndFiles(string $type, TableField $field, int $row_id, string $folderlink): array
    {
        $cloud = null;
        $files = [];
        if ($type == 'Google') {
            $mch = [];
            preg_match('/drive.google.com\/drive\/folders\/([^\/?]+)/i', $folderlink, $mch);
            $folderid = $mch[1];
            $cloud = $this->getCloud('Google', $field, $row_id);
            $files = $cloud
                ? $this->googleApi()->driveFindFiles($cloud->gettoken(), ['q' => "'$folderid' in parents"])
                : [];
        }
        if ($type == 'Dropbox') {
            $cloud = $this->getCloud('Dropbox', $field, $row_id);
            $files = $cloud
                ? $this->dropboxApi($cloud->cloud)->sharedFolderContent($cloud->id, $folderlink)
                : [];
        }
        if ($type == 'OneDrive') {
            $cloud = $this->getCloud('OneDrive', $field, $row_id);
            $files = $cloud
                ? $this->oneDriveApi()->listFolderOrFile($cloud->id, $folderlink)
                : [];
        }
        return [$cloud, $files];
    }

    /**
     * @param string $type
     * @param TableField $field
     * @param int $row_id
     * @return UserCloud|null
     */
    protected function getCloud(string $type, TableField $field, int $row_id)
    {
        $cloud = null;
        if ($field->_fetch_cloud_field) {
            $row = $this->dataService->getDirectRow($this->table, $row_id, ['none']);
            $cloud_id = $row[$field->_fetch_cloud_field->field];
            $cloud = $this->cloudRepo->getCloud($cloud_id ?? 0);
        } elseif ($field->fetch_one_cloud_id) {
            $cloud = $this->cloudRepo->getCloud($field->fetch_one_cloud_id);
        } else {
            $cloud = $this->cloudRepo->firstActiveCloud($this->user, $type);
        }

        return $cloud;
    }

    /**
     * @param array $newrow
     * @param array $oldrow
     * @throws Exception
     */
    public function fillRow(array $newrow, array $oldrow)
    {
        foreach ($this->remoteFiles->fieldsForRemote($this->table->id) as $field) {
            if (!empty($newrow['id'])) {
                $this->createOrUpdateRemotes($field, $newrow, $oldrow);
            }
        }
        $this->dataService->newTableVersion($this->table);
    }

    /**
     * @param TableField $field
     * @param array $newrow
     * @param array $oldrow
     * @throws Exception
     */
    protected function createOrUpdateRemotes(TableField $field, array $newrow, array $oldrow = [])
    {
        $newlink = $newrow[$field->_fetch_field->field] ?? '';
        $oldlink = $oldrow[$field->_fetch_field->field] ?? '';

        $cloudstate = '';
        if ($field->_fetch_cloud_field) {
            $newcloud = $newrow[$field->_fetch_cloud_field->field] ?? '';
            $oldcloud = $oldrow[$field->_fetch_cloud_field->field] ?? '';
            if ($newcloud != $oldcloud) {
                $cloudstate = $newcloud ? 'update' : 'remove';
            }
        }

        if ($newlink != $oldlink || $cloudstate) {
            $this->clear($field->id, $newrow['id']);
            if ($newlink && $cloudstate != 'remove') {
                $this->remoteFilesCreation($field, $newrow['id'], $newlink);
            }
        }
    }

    /**
     * @return GoogleApiModule
     */
    protected function googleApi()
    {
        return new GoogleApiModule('Google');
    }

    /**
     * @param string $type
     * @return DropBoxApiModule
     */
    protected function dropboxApi(string $type = '')
    {
        return new DropBoxApiModule($type);
    }

    /**
     * @return OneDriveApiModule
     */
    protected function oneDriveApi()
    {
        return new OneDriveApiModule('OneDrive');
    }
}