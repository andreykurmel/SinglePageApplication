<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableCalendarRight;
use Vanguard\Repositories\Tablda\TableCalendarRepository;

class TableCalendarService
{
    protected $service;
    protected $addonRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->addonRepo = new TableCalendarRepository();
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
     * @return TableCalendar
     */
    public function get(int $model_id)
    {
        return $this->addonRepo->get($model_id);
    }

    /**
     * @param array $data
     * @return TableCalendar
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
     * @param TableCalendar $calendar
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableCalendarRight
     */
    public function toggleCalendarRight(TableCalendar $calendar, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleCalendarRight($calendar, $table_permis_id, $can_edit);
    }

    /**
     * @param TableCalendar $calendar
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteCalendarRight(TableCalendar $calendar, int $table_permis_id)
    {
        return $this->addonRepo->deleteCalendarRight($calendar, $table_permis_id);
    }
}