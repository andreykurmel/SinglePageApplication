<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\Classes\TabldaUser;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\File\PostFileRequest;
use Vanguard\Http\Requests\Tablda\File\PutFileRequest;
use Vanguard\Http\Requests\Tablda\File\DeleteFileRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class FileController extends Controller
{
    private $tableService;
    private $fieldRepository;
    private $fileRepository;

    /**
     * FileController constructor.
     *
     * @param tableService $tableService
     */
    public function __construct(TableService $tableService, TableFieldRepository $fieldRepository, FileRepository $fileRepository)
    {
        $this->tableService = $tableService;
        $this->fieldRepository = $fieldRepository;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Get user`s table rows
     *
     * @param PostFileRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insert(PostFileRequest $request) {
        if ($request->special_params) {
            $user = auth()->user() ?: new User();
            $table_t = $this->tableService->getTable($request->table_id);
            $req_array = $request->all();
            $req_array['special_params'] = json_decode($req_array['special_params'], true);
            $this->authorizeForUser($user, 'load', [TableData::class, $table_t, HelperService::webHashFromReq($req_array)]);
        } else {
            $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);
        }

        return $this->fileRepository->insertFile($request->all(), $request->file('file'));
    }

    /**
     * Get user`s table rows
     *
     * @param PutFileRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PutFileRequest $request) {
        $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);

        return [ 'file' => $this->fileRepository->updateFile($request->all()) ];
    }

    /**
     * Delete file from user`s table row
     *
     * @param DeleteFileRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DeleteFileRequest $request) {
        $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);

        return ['status' => $this->fileRepository->deleteFile($request->all())];
    }

    /**
     * Check that user can modify column with files.
     *
     * @param $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function canModifyTableColumn($request) {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('update', [TableData::class, $table]);
    }

    /**
     * Check that user is owner for table.
     *
     * @param $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function canModifyTable($request) {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('isOwner', [TableData::class, $table]);
    }
}
