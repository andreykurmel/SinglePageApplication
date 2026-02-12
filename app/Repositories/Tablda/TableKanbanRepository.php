<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableKanbanGroupParam;
use Vanguard\Models\Table\TableKanbanRight;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Models\Table\TableKanbanSettings2Fields;
use Vanguard\Services\Tablda\HelperService;

class TableKanbanRepository
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
     * @return Collection
     */
    public function allKanbans(Table $table)
    {
        return $table->_kanban_settings()
            ->with('_fields_pivot', '_kanban_rights', '_group_params')
            ->get();
    }

    /**
     * @param int $kanban_id
     * @param bool $relations
     * @return TableKanbanSettings
     */
    public function getKanban(int $kanban_id, bool $relations = true)
    {
        $kanb = TableKanbanSettings::where('id', '=', $kanban_id)->first();
        if ($relations) {
            $kanb->load('_fields_pivot', '_kanban_rights', '_group_params');
        }
        return $kanb;
    }

    /**
     * @param array $data
     * @return TableKanbanSettings
     */
    public function insert(array $data)
    {
        return TableKanbanSettings::create($this->service->delSystemFields($data));
    }

    /**
     * @param int $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(int $model_id, array $data)
    {
        return TableKanbanSettings::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param int $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete(int $model_id)
    {
        return TableKanbanSettings::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param int $kanban_id
     * @param string $field
     * @param $val
     * @return mixed
     */
    public function updateKanban(int $kanban_id, string $field, $val)
    {
        return TableKanbanSettings::where('id', '=', $kanban_id)->update([
            $field => $val
        ]);
    }

    /**
     * @param int $kanban_id
     * @param int $field_id
     * @return void
     */
    public function attachIfNeeded(int $kanban_id, int $field_id)
    {
        TableKanbanSettings2Fields::updateOrCreate([
            'table_kanban_setting_id' => $kanban_id,
            'table_field_id' => $field_id,
        ], [
            'table_kanban_setting_id' => $kanban_id,
            'table_field_id' => $field_id,
        ]);
    }

    /**
     * @param int $kanban_id
     * @param int $field_id
     * @param string $setting
     * @param $val
     * @return void
     */
    public function changePivotFld(int $kanban_id, int $field_id, string $setting, $val)
    {
        $arr = [
            $setting => $val
        ];
        if ($setting == 'table_show_value') {
            $arr['table_show_name'] = $val;
            $arr['cell_border'] = $val;
        }
        if ($setting == 'table_show_value' && !$val) {
            $arr['is_start_table_popup'] = 0;
            $arr['is_table_field_in_popup'] = 0;
            $arr['is_hdr_lvl_one_row'] = 0;
        }

        TableKanbanSettings2Fields::where('table_kanban_setting_id', '=', $kanban_id)
            ->where('table_field_id', '=', $field_id)
            ->update($arr);
    }

    /**
     * @param TableKanbanSettings $kanban
     * @param int $table_permis_id
     * @param $can_edit
     * @return mixed
     */
    public function toggleKanbanRight(TableKanbanSettings $kanban, int $table_permis_id, $can_edit)
    {
        $right = $kanban->_kanban_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableKanbanRight::create([
                'table_kanban_id' => $kanban->id,
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
     * @param TableKanbanSettings $kanban
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteKanbanRight(TableKanbanSettings $kanban, int $table_permis_id)
    {
        return $kanban->_kanban_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }

    /**
     * @param int $from_kanban_id
     * @param int $to_kanban_id
     * @param bool $field_pivot
     * @return TableKanbanSettings
     */
    public function copyKanbanSett(int $from_kanban_id, int $to_kanban_id, bool $field_pivot)
    {
        $from = $this->getKanban($from_kanban_id);
        if ($field_pivot) {
            TableKanbanSettings::where('id', '=', $to_kanban_id)->update([
                'kanban_group_field_id' => $from->kanban_group_field_id,
            ]);
            TableKanbanSettings2Fields::where('table_kanban_setting_id', '=', $to_kanban_id)->delete();
            foreach ($from->_fields_pivot as $pivot) {
                TableKanbanSettings2Fields::create(array_merge(
                    $pivot->toArray(),
                    ['table_kanban_setting_id' => $to_kanban_id]
                ));
            }
        } else {
            TableKanbanSettings::where('id', '=', $to_kanban_id)->update(
                $from->only(TableKanbanSettings::$forCopy)
            );
        }
        return $this->getKanban($to_kanban_id);
    }

    /**
     * @param TableKanbanSettings $kanban
     * @param array $fields
     * @return TableKanbanGroupParam
     */
    public function addGroupParam(TableKanbanSettings $kanban, array $fields): TableKanbanGroupParam
    {
        return TableKanbanGroupParam::create([
            'table_kanban_id' => $kanban->id,
            'table_field_id' => $fields['table_field_id'],
            'stat' => $fields['stat'],
        ]);
    }

    /**
     * @param TableKanbanGroupParam $param
     * @param array $fields
     * @return bool|int
     */
    public function updateGroupParam(TableKanbanGroupParam $param, array $fields)
    {
        return TableKanbanGroupParam::where('id', '=', $param->id)->update([
            'table_field_id' => $fields['table_field_id'],
            'stat' => $fields['stat'],
        ]);
    }

    /**
     * @param TableKanbanGroupParam $param
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteGroupParam(TableKanbanGroupParam $param)
    {
        return TableKanbanGroupParam::where('id', '=', $param->id)->delete();
    }
}