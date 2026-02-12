<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Illuminate\Support\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableMap;
use Vanguard\Models\Table\TableMapRight;
use Vanguard\Repositories\Tablda\TableData\MapRepository;

class TableMapService
{
    protected $service;
    protected $addonRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->addonRepo = new MapRepository();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table, $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
    }

    /**
     * @param int $model_id
     * @return TableMap
     */
    public function get($model_id)
    {
        return $this->addonRepo->get($model_id);
    }

    /**
     * @param array $data
     * @return TableMap
     */
    public function insert(array $data)
    {
        return $this->addonRepo->insert($data);
    }

    /**
     * @param $model_id
     * @param array $data
     * @return mixed
     */
    public function update($model_id, array $data)
    {
        return $this->addonRepo->update($model_id, $data);
    }

    /**
     * @param $table_email_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($table_email_id)
    {
        return $this->addonRepo->delete($table_email_id);
    }

    /**
     * @param int $model_id
     * @param int $field_id
     * @param array $data
     * @return Collection
     * @throws Exception
     */
    public function syncFieldSpecific(int $model_id, int $field_id, array $data): Collection
    {
        $this->addonRepo->syncFieldSpecific($model_id, $field_id, $data);
        $map = $this->get($model_id);
        return $map->_map_field_settings;
    }

    /**
     * @param int $from_add_id
     * @param int $to_add_id
     * @return TableMap
     */
    public function copyAdn(int $from_add_id, int $to_add_id)
    {
        return $this->addonRepo->copyAdn($from_add_id, $to_add_id);
    }

    /**
     * @param TableMap $map
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableMapRight
     */
    public function toggleMapRight(TableMap $map, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleMapRight($map, $table_permis_id, $can_edit);
    }

    /**
     * @param TableMap $map
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteMapRight(TableMap $map, int $table_permis_id)
    {
        return $this->addonRepo->deleteMapRight($map, $table_permis_id);
    }
}