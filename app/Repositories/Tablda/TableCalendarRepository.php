<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableCalendarRight;
use Vanguard\Services\Tablda\HelperService;

class TableCalendarRepository
{
    protected $service;

    /**
     * TableAlertRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        if (!$table->_calendar_addons->count()) {
            $this->insert(['table_id' => $table->id]);
        }
        $table->load([
            '_calendar_addons' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with('_calendar_rights');
            }
        ]);
    }

    /**
     * @param int $model_id
     * @return TableCalendar
     */
    public function get(int $model_id)
    {
        return TableCalendar::where('id', '=', $model_id)->first();
    }

    /**
     * @param array $data
     * @return TableCalendar
     */
    public function insert(array $data)
    {
        $data = $this->dataDef($data);
        return TableCalendar::create($this->service->delSystemFields($data));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function dataDef(array $data)
    {
        $data['name'] = $data['name'] ?? 'Calendar';
        return $data;
    }

    /**
     * @param $model_id
     * @param array $data
     * @return mixed
     */
    public function update($model_id, array $data)
    {
        $data = $this->dataDef($data);
        return TableCalendar::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $twilio_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($twilio_id)
    {
        return TableCalendar::where('id', '=', $twilio_id)
            ->delete();
    }

    /**
     * @param TableCalendar $calendar
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableCalendarRight
     */
    public function toggleCalendarRight(TableCalendar $calendar, int $table_permis_id, $can_edit)
    {
        $right = $calendar->_calendar_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableCalendarRight::create([
                'table_calendar_id' => $calendar->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableCalendar $calendar
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteCalendarRight(TableCalendar $calendar, int $table_permis_id)
    {
        return $calendar->_calendar_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}