<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Jobs\WatchRemoteFiles;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\RemoteFilesRepository;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class RemoteFileController extends Controller
{
    /**
     * @var RemoteFilesRepository
     */
    protected $remoteRepo;

    /**
     *
     */
    public function __construct()
    {
        $this->remoteRepo = new RemoteFilesRepository();
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $request->table_field_id ? $this->canModify('update', $request) : $this->canModify('isOwner', $request);
        return ['status' => $this->remoteRepo->update($request->id, $request->notes)];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $request->table_field_id ? $this->canModify('update', $request) : $this->canModify('isOwner', $request);
        return ['status' => $this->remoteRepo->delete($request->table_id, $request->table_field_id, $request->row_id, $request->id, !!$request->with_cloud)];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function rowResync(Request $request)
    {
        $request->table_field_id ? $this->canModify('update', $request) : $this->canModify('isOwner', $request);
        $table_t = (new TableService())->getTable($request->table_id);
        $row = (new TableDataService())->getDirectRow($table_t, $request->row_id, ['none']);
        $job = new WatchRemoteFiles($request->table_id, null, $row->toArray());
        try {
            $job->handle();
        } catch (\Exception $e) {}
        return ['status' => true];
    }

    /**
     * @param $type
     * @param $request
     * @throws AuthorizationException
     */
    protected function canModify($type, $request)
    {
        $user = auth()->user() ?: new User();
        $table_t = (new TableService())->getTable($request->table_id);
        $this->authorizeForUser($user, $type, [TableData::class, $table_t, $request->all()]);
    }
}
