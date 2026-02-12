<?php


namespace Vanguard\Services\Tablda;


use Vanguard\Models\File;
use Vanguard\Models\RemoteFile;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\RemoteFilesRepository;

class FileService
{
    protected $remoteFileRepository;
    protected $fileRepository;

    /**
     * TableDataService constructor.
     */
    public function __construct()
    {
        $this->remoteFileRepository = new RemoteFilesRepository();
        $this->fileRepository = new FileRepository();
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     * @return string
     */
    public function getContent(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null): string
    {
        $path = $this->getFullPath($table_id, $field_id, $row_id, $direct_id);
        return $path ? file_get_contents($path) : '';
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     * @return string
     */
    public function getFullPath(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null): string
    {
        if ($file = $this->fileRepository->getSql($table_id, $field_id, $row_id ? [$row_id] : null, $direct_id)->first()) {
            return storage_path('app/public/' . $file->filepath . $file->filename);
        }
        if ($file = $this->remoteFileRepository->getSql($table_id, $field_id, $row_id, $direct_id)->first()) {
            return $file->remote_link;
        }
        return '';
    }

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param int|null $direct_id
     * @return RemoteFile|File|null
     */
    public function getFile(int $table_id, int $field_id = null, int $row_id = null, int $direct_id = null)
    {
        if ($file = $this->fileRepository->getSql($table_id, $field_id, $row_id ? [$row_id] : null, $direct_id)->first()) {
            return $file;
        }
        if ($file = $this->remoteFileRepository->getSql($table_id, $field_id, $row_id, $direct_id)->first()) {
            return $file;
        }
        return null;
    }

    /**
     * @param Table $table
     * @param array $rows_correspondence
     * @return void
     */
    public function copyAttachForRows(Table $table, array $rows_correspondence)
    {
        $this->fileRepository->copyAttachForRows($table, $table, $rows_correspondence);
        $this->remoteFileRepository->copyAttachForRows($table, $table, $rows_correspondence);
    }

    /**
     * @param Table $table_from
     * @param Table $table_to
     * @param array $rows_correspondence
     * @param array $fields_correspondence
     * @return void
     */
    public function copyAttachForRowsSpecial(Table $table_from, Table $table_to, array $rows_correspondence, array $fields_correspondence)
    {
        $this->fileRepository->copyAttachForRows($table_from, $table_to, $rows_correspondence, $fields_correspondence);
        $this->remoteFileRepository->copyAttachForRows($table_from, $table_to, $rows_correspondence, $fields_correspondence);
    }
}