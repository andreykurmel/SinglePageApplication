<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableGantt;
use Vanguard\Services\Tablda\TableGanttService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableGanttController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableGanttService
     */
    protected $ganttService;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->ganttService = new TableGanttService();
    }

    /**
     * @param Request $request
     * @return TableGantt
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'gantt');
        $this->ganttService->insert(array_merge($request->fields, ['table_id' => $table->id]));
        $this->ganttService->loadForTable($table, auth()->id());
        return $table->_gantt_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $gantt = $this->ganttService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $gantt->_table;
        $this->canEditAddonItem($table, $gantt->_gantt_rights());
        $this->ganttService->update($request->model_id, array_merge($request->fields, ['table_id' => $table->id]));
        $this->ganttService->loadForTable($table, auth()->id());
        return $table->_gantt_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $gantt = $this->ganttService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $gantt->_table;
        $this->canEditAddonItem($table, $gantt->_gantt_rights());
        $this->ganttService->delete($request->model_id);
        $this->ganttService->loadForTable($table, auth()->id());
        return $table->_gantt_addons;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function syncSetting(Request $request)
    {
        $gantt = $this->ganttService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($gantt->_table, $gantt->_gantt_rights());
        return $this->ganttService->syncFieldSpecific($request->model_id, $request->field_id, $request->datas);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableGanttRight
     * @throws AuthorizationException
     */
    public function toggleGanttRight(Request $request)
    {
        $gantt = $this->ganttService->get($request->gantt_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $gantt->_table]);
        return $this->ganttService->toggleGanttRight($gantt, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delGanttRight(Request $request)
    {
        $gantt = $this->ganttService->get($request->gantt_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $gantt->_table]);
        return $this->ganttService->deleteGanttRight($gantt, $request->permission_id);
    }
}
