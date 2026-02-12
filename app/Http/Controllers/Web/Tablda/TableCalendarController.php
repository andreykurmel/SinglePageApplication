<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableCalendarRight;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\TableCalendarService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableCalendarController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableCalendarService
     */
    protected $calendarService;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->calendarService = new TableCalendarService();
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'calendar');
        $this->calendarService->insert(array_merge($request->fields, ['table_id' => $table->id]));
        $this->calendarService->loadForTable($table, auth()->id());
        return $table->_calendar_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $calendar = $this->calendarService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $calendar->_table;
        $this->canEditAddonItem($table, $calendar->_calendar_rights());
        $this->calendarService->update($request->model_id, array_merge($request->fields, ['table_id' => $table->id]));
        $this->calendarService->loadForTable($table, auth()->id());
        return $table->_calendar_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $calendar = $this->calendarService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $calendar->_table;
        $this->canEditAddonItem($table, $calendar->_calendar_rights());
        $this->calendarService->delete($request->model_id);
        $this->calendarService->loadForTable($table, auth()->id());
        return $table->_calendar_addons;
    }

    /**
     * @param Request $request
     * @return TableCalendarRight
     * @throws AuthorizationException
     */
    public function toggleCalendarRight(Request $request)
    {
        $calendar = $this->calendarService->get($request->calendar_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $calendar->_table]);
        return $this->calendarService->toggleCalendarRight($calendar, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delCalendarRight(Request $request)
    {
        $calendar = $this->calendarService->get($request->calendar_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $calendar->_table]);
        return $this->calendarService->deleteCalendarRight($calendar, $request->permission_id);
    }
}
