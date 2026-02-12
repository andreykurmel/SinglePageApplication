<?php

namespace Vanguard\Repositories\Tablda;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableKanbanSettings2Fields;
use Vanguard\Models\Table\TableSimplemap;
use Vanguard\Models\Table\TableSimplemapRight;
use Vanguard\Models\Table\TableSimplemaps2Fields;
use Vanguard\Services\Tablda\HelperService;

class TableSimplemapRepository
{
    protected $service;

    /**
     * TableRepository constructor.
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
        $table->load([
            '_simplemaps' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with('_simplemap_rights', '_fields_pivot');
            }
        ]);
    }

    /**
     * @param $table_simplemap_id
     * @return TableSimplemap|null
     */
    public function getTableSimplemap($table_simplemap_id)
    {
        return TableSimplemap::where('id', '=', $table_simplemap_id)->first();
    }

    /**
     * @param int $table_id
     * @param array $data
     * @return Model|TableSimplemap
     */
    public function addTableSimplemap(int $table_id, array $data)
    {
        $data['multirec_style'] = $data['multirec_style'] ?? 'tabs';

        return TableSimplemap::create(array_merge($data, [
            'table_id' => $table_id,
            'user_id' => auth()->id(),
        ]));
    }

    /**
     * @param int $table_simplemap_id
     * @param array $data
     * @return bool|int
     */
    public function updateTableSimplemap(int $table_simplemap_id, array $data)
    {
        return TableSimplemap::where('id', '=', $table_simplemap_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_simplemap_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTableSimplemap($table_simplemap_id)
    {
        return TableSimplemap::where('id', '=', $table_simplemap_id)->delete();
    }

    /**
     * @param TableSimplemap $simplemap
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableSimplemapRight
     */
    public function toggleSimplemapRight(TableSimplemap $simplemap, int $table_permis_id, $can_edit)
    {
        $right = $simplemap->_simplemap_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableSimplemapRight::create([
                'table_simplemap_id' => $simplemap->id,
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
     * @param TableSimplemap $simplemap
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteSimplemapRight(TableSimplemap $simplemap, int $table_permis_id)
    {
        return $simplemap->_simplemap_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }

    /**
     * @param int $simplemap_id
     * @param int $field_id
     * @param string $setting
     * @param $val
     * @return void
     */
    public function changePivotFld(int $simplemap_id, int $field_id, string $setting, $val)
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

        TableSimplemaps2Fields::updateOrCreate([
            'table_simplemap_id' => $simplemap_id,
            'table_field_id' => $field_id,
        ], $arr);
    }

    /**
     * @param int $from_simplemap_id
     * @param int $to_simplemap_id
     * @param bool $field_pivot
     * @return TableSimplemap
     */
    public function copySimplemapSett(int $from_simplemap_id, int $to_simplemap_id, bool $field_pivot)
    {
        $from = $this->getTableSimplemap($from_simplemap_id);
        if ($field_pivot) {
            TableSimplemaps2Fields::where('table_simplemap_id', '=', $to_simplemap_id)->delete();
            foreach ($from->_fields_pivot as $pivot) {
                TableSimplemaps2Fields::create(array_merge(
                    $pivot->toArray(),
                    ['table_simplemap_id' => $to_simplemap_id]
                ));
            }
        } else {
            TableSimplemap::where('id', '=', $to_simplemap_id)->update(
                $from->only(TableSimplemap::$forCopy)
            );
        }
        return $this->getTableSimplemap($to_simplemap_id);
    }
}