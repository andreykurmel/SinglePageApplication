<?php

namespace Vanguard\Repositories\Tablda;

use Vanguard\Models\Table\TableKanbanSettings;
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
     * @param int $kanban_id
     * @param bool $relations
     * @return mixed
     */
    public function getKanban(int $kanban_id, bool $relations = true)
    {
        $kanb = TableKanbanSettings::where('id', '=', $kanban_id)->first();
        if ($relations) {
            $kanb->load('_fields_pivot');
        }
        return $kanb;
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
     * @param bool $val
     */
    public function attachDetachFld(int $kanban_id, int $field_id, bool $val)
    {
        $kanb = TableKanbanSettings::where('id', '=', $kanban_id)->first();
        if ($val) {
            $kanb->_columns()->attach($field_id);
        } else {
            $kanb->_columns()->detach($field_id);
        }
    }

    /**
     * @param int $kanban_id
     * @param int $field_id
     * @param string $setting
     * @param $val
     */
    public function changePivotFld(int $kanban_id, int $field_id, string $setting, $val)
    {
        $kanb = TableKanbanSettings::where('id', '=', $kanban_id)->first();
        $kanb->_fields_pivot()->where('table_field_id', '=', $field_id)->update([
            $setting => $val
        ]);
    }
}