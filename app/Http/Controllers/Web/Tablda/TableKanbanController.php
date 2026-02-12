<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableKanbanRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableKanbanController extends Controller
{
    /**
     * @var TableKanbanRepository
     */
    protected $kanbanRepo;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->kanbanRepo = new TableKanbanRepository();
    }

    /**
     * @param Request $request
     * @return Collection
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->kanbanRepo->insert(array_merge($request->fields, ['table_id' => $table->id]));
        return $this->kanbanRepo->allKanbans($table);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $kanban = $this->kanbanRepo->getKanban($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $kanban->_table;
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->kanbanRepo->update($request->model_id, array_merge($request->fields, ['table_id' => $table->id]));
        return $this->kanbanRepo->allKanbans($table);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $kanban = $this->kanbanRepo->getKanban($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $kanban->_table;
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        $this->kanbanRepo->delete($request->model_id);
        return $this->kanbanRepo->allKanbans($table);
    }
}
