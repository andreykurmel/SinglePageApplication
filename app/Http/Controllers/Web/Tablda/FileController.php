<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\File\DeleteFileRequest;
use Vanguard\Http\Requests\Tablda\File\PostFileRequest;
use Vanguard\Http\Requests\Tablda\File\PutFileRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class FileController extends Controller
{
    private $tableService;
    private $fileRepository;

    /**
     * FileController constructor.
     *
     * @param tableService $tableService
     */
    public function __construct(TableService $tableService, FileRepository $fileRepository)
    {
        $this->tableService = $tableService;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Get user`s table rows
     *
     * @param PostFileRequest $request
     * @return array|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insert(PostFileRequest $request)
    {
        if ($request->table_id == 'temp') {
            return $this->fileRepository->saveInTemp($request->all(), $request->file('file'));
        } else {
            $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);
            return $this->fileRepository->insertFile($request->all(), $request->file('file'));
        }
    }

    /**
     * Get user`s table rows
     *
     * @param PutFileRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PutFileRequest $request)
    {
        $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);

        return ['file' => $this->fileRepository->updateFile($request->all())];
    }

    /**
     * Delete file from user`s table row
     *
     * @param DeleteFileRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DeleteFileRequest $request)
    {
        $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);

        return ['status' => $this->fileRepository->deleteFile($request->all())];
    }

    /**
     * Check that user can modify column with files.
     *
     * @param $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function canModifyTableColumn($request)
    {
        $this->canModify('update', $request);
    }

    /**
     * Check that user is owner for table.
     *
     * @param $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function canModifyTable($request)
    {
        $this->canModify('isOwner', $request);
    }

    /**
     * @param $type
     * @param $request
     */
    protected function canModify($type, $request)
    {
        $user = auth()->user() ?: new User();
        $table_t = $this->tableService->getTable($request->table_id);

        if (in_array($table_t->db_name, ['stim_app_view_feedback_results'])) {
            return true; //files are available
        }

        $req_array = $request->all();
        $req_array['special_params'] = $req_array['special_params'] ?? [];
        if (!is_array($req_array['special_params'])) {
            $req_array['special_params'] = json_decode($req_array['special_params'], true);
        }
        $this->authorizeForUser($user, $type, [TableData::class, $table_t, $req_array]);
    }


}
