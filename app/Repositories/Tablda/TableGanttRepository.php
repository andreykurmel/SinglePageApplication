<?php

namespace Vanguard\Repositories\Tablda;

use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableGantt;
use Vanguard\Models\Table\TableGanttSetting;
use Vanguard\Models\Table\TableGanttRight;
use Vanguard\Services\Tablda\HelperService;

class TableGanttRepository
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
        if (!$table->_gantt_addons->count()) {
            $this->insert(['table_id' => $table->id]);
        }
        $table->load([
            '_gantt_addons' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with([
                    '_specifics',
                    '_gantt_rights',
                ]);
            }
        ]);
    }

    /**
     * @param int $model_id
     * @return TableGantt
     */
    public function get(int $model_id)
    {
        return TableGantt::where('id', '=', $model_id)->first();
    }

    /**
     * @param array $data
     * @return TableGantt
     */
    public function insert(array $data)
    {
        $data = $this->dataDef($data);
        return TableGantt::create($this->service->delSystemFields($data));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function dataDef(array $data)
    {
        $data['name'] = $data['name'] ?? 'Gantt';
        return $data;
    }

    /**
     * @param int $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(int $model_id, array $data)
    {
        $data = $this->dataDef($data);
        return TableGantt::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param int $model_id
     * @return bool|int|null
     * @throws \Exception
     */
    public function delete(int $model_id)
    {
        return TableGantt::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param int $model_id
     * @param int $field_id
     * @param array $data
     * @return void
     */
    public function syncFieldSpecific(int $model_id, int $field_id, array $data): void
    {
        $ch_field = $data['_changed_field'] ?? '';

        $data = collect($data)->only(TableGanttSetting::booleans())->toArray();

        //data preparations
        if (in_array($ch_field, ['is_gantt_main_group','is_gantt_parent_group','is_gantt_group']) && ($data[$ch_field] ?? '')) {
            $data['is_gantt_left_header'] = $data['is_gantt_group'] ?? null;
        }
        //dd($ch_field, $data);

        //sync radio fields with other rows
        if (in_array($ch_field, TableGanttSetting::radios())) {
            TableGanttSetting::where('table_gantt_id', '=', $model_id)->update([
                $ch_field => null,
            ]);
        }

        //update data
        TableGanttSetting::updateOrCreate([
            'table_gantt_id' => $model_id,
            'table_field_id' => $field_id,
        ], $data);
    }

    /**
     * @param TableGantt $gantt
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableGanttRight
     */
    public function toggleGanttRight(TableGantt $gantt, int $table_permis_id, $can_edit)
    {
        $right = $gantt->_gantt_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableGanttRight::create([
                'table_gantt_id' => $gantt->id,
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
     * @param TableGantt $gantt
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteGanttRight(TableGantt $gantt, int $table_permis_id)
    {
        return $gantt->_gantt_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}