<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\File\DeleteFileRequest;
use Vanguard\Http\Requests\Tablda\File\PostFileRequest;
use Vanguard\Http\Requests\Tablda\File\PutFileRequest;
use Vanguard\Http\Requests\Tablda\File\SingleFileRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\FileRepository;
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
     * @throws AuthorizationException
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
     * Check that user can modify column with files.
     *
     * @param $request
     * @throws AuthorizationException
     */
    protected function canModifyTableColumn($request)
    {
        $this->canModify('update', $request);
    }

    /**
     * @param $type
     * @param $request
     */
    protected function canModify($type, $request)
    {
        $user = auth()->user() ?: new User();
        $table_t = $this->tableService->getTable($request->table_id);

        if (in_array($table_t->db_name, ['stim_app_view_feedback_results','table_report_templates'])) {
            return; //files are available
        }

        $req_array = $request->all();
        $req_array['special_params'] = $req_array['special_params'] ?? [];
        if (!is_array($req_array['special_params'])) {
            $req_array['special_params'] = json_decode($req_array['special_params'], true);
        }
        $this->authorizeForUser($user, $type, [TableData::class, $table_t, $req_array]);
    }

    /**
     * Check that user is owner for table.
     *
     * @param $request
     * @throws AuthorizationException
     */
    protected function canModifyTable($request)
    {
        $this->canModify('isOwner', $request);
    }

    /**
     * Get user`s table rows
     *
     * @param PutFileRequest $request
     * @return array
     * @throws AuthorizationException
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
     * @throws AuthorizationException
     */
    public function delete(DeleteFileRequest $request)
    {
        $request->table_field_id ? $this->canModifyTableColumn($request) : $this->canModifyTable($request);

        return ['status' => $this->fileRepository->deleteFile($request->id)];
    }

    /**
     * @param SingleFileRequest $request
     * @return array
     * @throws AuthorizationException
     */
    public function moveToCloud(SingleFileRequest $request)
    {
        $this->canModifyTableColumn($request);
        return ['remote_file' => $this->fileRepository->moveToCloud($request->id)];
    }

    /**
     * @param SingleFileRequest $request
     * @return array
     * @throws AuthorizationException
     */
    public function storeChartImage(Request $request)
    {
        $this->canModifyTable($request);

        $path = 'bi_images_report/'.auth()->id().'/'.$request->table_id.'/'.$request->row_id.'/';
        $name = $request->chart_id.'.png';

        \Storage::delete($path.$name);

        $image = $request->file('chart_image');
        $image->storeAs($path, $name);

        return ['status' => 'ok'];
    }

}
