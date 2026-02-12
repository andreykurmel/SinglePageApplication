<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableMap;
use Vanguard\Services\Tablda\TableMapService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableMapController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableMapService
     */
    protected $mapService;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->mapService = new TableMapService();
    }

    /**
     * @param Request $request
     * @return TableMap
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'map');
        $this->mapService->insert(array_merge($request->fields, ['table_id' => $table->id]));
        $this->mapService->loadForTable($table, $user->id);
        return $table->_map_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $map = $this->mapService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $map->_table;
        $this->canEditAddonItem($table, $map->_map_rights());
        $this->mapService->update($request->model_id, array_merge($request->fields, ['table_id' => $table->id]));
        $this->mapService->loadForTable($table, $user->id);
        return $table->_map_addons;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $map = $this->mapService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $map->_table;
        $this->canEditAddonItem($table, $map->_map_rights());
        $this->mapService->delete($request->model_id);
        $this->mapService->loadForTable($table, $user->id);
        return $table->_map_addons;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function syncSetting(Request $request)
    {
        $map = $this->mapService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($map->_table, $map->_map_rights());
        return $this->mapService->syncFieldSpecific($request->model_id, $request->field_id, $request->fields);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function copySetting(Request $request)
    {
        $fr = $this->mapService->get($request->from_adn_id);
        $to = $this->mapService->get($request->to_adn_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($fr->_table, $fr->_map_rights());
        $this->canEditAddonItem($to->_table, $to->_map_rights());
        return $this->mapService->copyAdn($request->from_adn_id, $request->to_adn_id);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableMapRight
     * @throws AuthorizationException
     */
    public function toggleMapRight(Request $request)
    {
        $map = $this->mapService->get($request->map_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $map->_table]);
        return $this->mapService->toggleMapRight($map, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delMapRight(Request $request)
    {
        $map = $this->mapService->get($request->map_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $map->_table]);
        return $this->mapService->deleteMapRight($map, $request->permission_id);
    }
}
