<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Illuminate\Support\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableGantt;
use Vanguard\Models\Table\TableGanttRight;
use Vanguard\Repositories\Tablda\TableGanttRepository;

class TableGanttService
{
    protected $service;
    protected $addonRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->addonRepo = new TableGanttRepository();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
    }

    /**
     * @param int $model_id
     * @return TableGantt
     */
    public function get(int $model_id)
    {
        return $this->addonRepo->get($model_id);
    }

    /**
     * @param array $data
     * @return TableGantt
     */
    public function insert(array $data)
    {
        return $this->addonRepo->insert($data);
    }

    /**
     * @param $twilio_id
     * @param array $data
     * @return mixed
     */
    public function update($twilio_id, array $data)
    {
        return $this->addonRepo->update($twilio_id, $data);
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
        $gantt = $this->get($model_id);
        return $gantt->_specifics;
    }

    /**
     * @param TableGantt $gantt
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableGanttRight
     */
    public function toggleGanttRight(TableGantt $gantt, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleGanttRight($gantt, $table_permis_id, $can_edit);
    }

    /**
     * @param TableGantt $gantt
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteGanttRight(TableGantt $gantt, int $table_permis_id)
    {
        return $this->addonRepo->deleteGanttRight($gantt, $table_permis_id);
    }
}