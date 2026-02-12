<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableSimplemap;
use Vanguard\Models\Table\TableSimplemapRight;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableSimplemapRepository;

class TableSimplemapController extends Controller
{
    use CanEditAddon;

    protected $tableRepository;
    protected $repoSimplemap;

    /**
     * TableBackupsController constructor.
     *
     * @param TableRepository $tableRepository
     */
    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = new TableRepository();
        $this->repoSimplemap = new TableSimplemapRepository();
    }

    /**
     * @param Request $request
     * @return TableSimplemap[]
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = $this->tableRepository->getTable($request->table_id);
        $this->canEditAddon($table, 'simplemap');
        $this->repoSimplemap->addTableSimplemap($table->id, $request->fields);

        $this->repoSimplemap->loadForTable($table, auth()->id());
        return $table->_simplemaps;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $table_simplemap = $this->repoSimplemap->getTableSimplemap($request->model_id);
        $table = $table_simplemap->_table;
        $this->canEditAddonItem($table_simplemap->_table, $table_simplemap->_simplemap_rights());
        $this->repoSimplemap->updateTableSimplemap($table_simplemap->id, array_merge($request->fields, ['table_id' => $table->id]));

        $this->repoSimplemap->loadForTable($table, auth()->id());
        return $table->_simplemaps;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $table_simplemap = $this->repoSimplemap->getTableSimplemap($request->model_id);
        $table = $table_simplemap->_table;
        $this->canEditAddonItem($table_simplemap->_table, $table_simplemap->_simplemap_rights());
        $this->repoSimplemap->deleteTableSimplemap($table_simplemap->id);

        $this->repoSimplemap->loadForTable($table, auth()->id());
        return $table->_simplemaps;
    }

    /**
     * @param Request $request
     * @return TableSimplemapRight
     * @throws AuthorizationException
     */
    public function toggleSimplemapRight(Request $request)
    {
        $simplemap = $this->repoSimplemap->getTableSimplemap($request->simplemap_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $simplemap->_table]);
        return $this->repoSimplemap->toggleSimplemapRight($simplemap, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delSimplemapRight(Request $request)
    {
        $simplemap = $this->repoSimplemap->getTableSimplemap($request->simplemap_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $simplemap->_table]);
        return $this->repoSimplemap->deleteSimplemapRight($simplemap, $request->permission_id);
    }

    /**
     * Update Column in Table Simplemap Field
     *
     * @param Request $request
     * @return mixed
     */
    public function updateColumnInSimplemap(Request $request)
    {
        $simplemap = $this->repoSimplemap->getTableSimplemap($request->simplemap_id);
        $table = $simplemap->_table;
        $this->canEditAddonItem($simplemap->_table, $simplemap->_simplemap_rights());

        $this->repoSimplemap->changePivotFld(
            $simplemap->id,
            $request->field_id,
            $request->setting,
            $request->val
        );

        $this->repoSimplemap->loadForTable($table, auth()->id());
        return $table->_simplemaps->where('id', $simplemap->id)->first();
    }

    /**
     * Copy Table Simplemap
     *
     * @param Request $request
     * @return mixed
     */
    public function copySimplemapSett(Request $request)
    {
        $simplemap = $this->repoSimplemap->getTableSimplemap($request->to_simplemap_id);
        $table = $simplemap->_table;
        $this->canEditAddonItem($simplemap->_table, $simplemap->_simplemap_rights());
        $this->repoSimplemap->copySimplemapSett($request->from_simplemap_id, $request->to_simplemap_id, !!$request->field_pivot);

        $this->repoSimplemap->loadForTable($table, auth()->id());
        return $table->_simplemaps;
    }
}
