<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableSavedFilter;
use Vanguard\Repositories\Tablda\TableSavedFilterRepository;
use Vanguard\Services\Tablda\TableService;

class TableSavedFilterController extends Controller
{
    /**
     * @var TableSavedFilterRepository
     */
    protected $filterRepo;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->filterRepo = new TableSavedFilterRepository();
    }

    /**
     * @param Model $model
     * @return void
     */
    protected function auth(Model $model): void
    {
        abort_if($model->user_id != auth()->id(), 401, 'Unauthorized');
    }

    /**
     * @param Table $table
     * @return TableSavedFilter[]
     */
    protected function response(Table $table)
    {
        $table->load('_saved_filters:id,table_id,user_id,name,related_colgroup_id');
        return $table->_saved_filters;
    }

    /**
     * @param Request $request
     * @return TableSavedFilter
     */
    public function get(Request $request)
    {
        $model = $this->filterRepo->get($request->model_id);
        $this->auth($model);
        return $model;
    }

    /**
     * @param Request $request
     * @return TableSavedFilter[]
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $this->filterRepo->insert($table, $request->fields);
        return $this->response($table);
    }

    /**
     * @param Request $request
     * @return TableSavedFilter[]
     */
    public function update(Request $request)
    {
        $model = $this->filterRepo->get($request->model_id);
        $table = $model->_table;
        $this->auth($model);
        $this->filterRepo->update($table, $request->model_id, $request->fields);
        return $this->response($table);
    }

    /**
     * @param Request $request
     * @return TableSavedFilter[]
     * @throws Exception
     */
    public function delete(Request $request)
    {
        $model = $this->filterRepo->get($request->model_id);
        $table = $model->_table;
        $this->auth($model);
        $this->filterRepo->delete($request->model_id);
        return $this->response($table);
    }
}
