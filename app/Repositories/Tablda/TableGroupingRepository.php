<?php

namespace Vanguard\Repositories\Tablda;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableGrouping;
use Vanguard\Models\Table\TableGroupingField;
use Vanguard\Models\Table\TableGroupingFieldStat;
use Vanguard\Models\Table\TableGroupingRelatedField;
use Vanguard\Models\Table\TableGroupingRight;
use Vanguard\Models\Table\TableGroupingStat;
use Vanguard\Services\Tablda\HelperService;

class TableGroupingRepository
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
            '_groupings' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with([
                    '_global_stats',
                    '_grouping_rights',
                    '_settings' => function ($s) {
                        $s->with('_field_stats');
                    },
                    '_col_group' => function ($s) {
                        $s->with('_fields');
                    },
                    '_gr_related_fields' => function ($s) {
                        $s->orderBy('pivot_fld_order');
                        $s->select('table_fields.id', 'table_fields.field');
                    },
                ]);
            }
        ]);
    }

    /**
     * @param int $grouping_id
     * @param array $ids
     * @param int $visible
     * @return void
     */
    public function updateRelatedFields(int $grouping_id, array $ids, int $visible): void
    {
        TableGroupingRelatedField::where('grouping_id', $grouping_id)
            ->whereIn('field_id', $ids)
            ->update(['fld_visible' => $visible]);
    }

    /**
     * @param int $grouping_id
     * @param int $before_id
     * @param int $after_id
     * @return void
     */
    public function reorderRelatedField(int $grouping_id, int $before_id, int $after_id): void
    {
        $before = TableGroupingRelatedField::where('grouping_id', $grouping_id)->where('field_id', $before_id)->first();
        $after = TableGroupingRelatedField::where('grouping_id', $grouping_id)->where('field_id', $after_id)->first();
        if ($before && $after) {
            $order = $after->fld_order;
            $before->update(['fld_order' => $order]);

            $columns = TableGroupingRelatedField::where('grouping_id', $grouping_id)
                ->where('fld_order', '>', $order)
                ->orderBy('fld_order')
                ->get();
            $columns->prepend($after);

            foreach ($columns as $col) {
                $order += 1;
                $col->update(['fld_order' => $order]);
            }
        }
    }

    /**
     * @param array $limitByGroups
     * @param array $limitByCols
     * @return void
     */
    public function syncRelatedFields(array $limitByGroups = [], array $limitByCols = []): void
    {
        TableGrouping::query()
            ->when($limitByGroups, function ($query) use ($limitByGroups) {
                return $query->whereIn('id', $limitByGroups);
            })
            ->when($limitByCols, function ($query) use ($limitByCols) {
                return $query->whereIn('rg_colgroup_id', $limitByCols);
            })
            ->with('_table._fields', '_col_group._fields')
            ->chunk(10, function ($groupings) {
                foreach ($groupings as $grouping) {
                    $fields = $grouping->rg_colgroup_id && $grouping->_col_group
                        ? $grouping->_col_group->_fields
                        : $grouping->_table->_fields;

                    //Remove old
                    TableGroupingRelatedField::where('grouping_id', $grouping->id)
                        ->whereNotIn('field_id', $fields->pluck('id'))
                        ->delete();

                    //Add New / Update
                    foreach ($fields as $field) {
                        TableGroupingRelatedField::updateOrCreate([
                            'grouping_id' => $grouping->id,
                            'field_id' => $field->id,
                        ], [
                            'fld_visible' => 1,
                            'fld_order' => $field->order,
                        ]);
                    }
                }
            });
    }

    /**
     * @param $table_grouping_id
     * @return TableGrouping|null
     */
    public function getTableGrouping($table_grouping_id)
    {
        return TableGrouping::where('id', '=', $table_grouping_id)->first();
    }

    /**
     * @param int $table_id
     * @param array $data
     * @return Model|TableGrouping
     */
    public function addTableGrouping(int $table_id, array $data)
    {
        $data['rg_data_range'] = $data['rg_data_range'] ?? '0';
        $data['rg_alignment'] = $data['rg_alignment'] ?? 'start';
        return TableGrouping::create(array_merge($data, ['table_id' => $table_id]));
    }

    /**
     * @param int $table_grouping_id
     * @param array $data
     * @return bool|int
     */
    public function updateTableGrouping(int $table_grouping_id, array $data)
    {
        $res = TableGrouping::where('id', '=', $table_grouping_id)
            ->update($this->service->delSystemFields($data));

        $this->syncRelatedFields([$table_grouping_id]);

        return $res;
    }

    /**
     * @param $table_grouping_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTableGrouping($table_grouping_id)
    {
        return TableGrouping::where('id', '=', $table_grouping_id)->delete();
    }

    /**
     * @param $table_grouping_id
     * @return TableGroupingField|null
     */
    public function getTableGroupingField($table_grouping_id)
    {
        return TableGroupingField::where('id', '=', $table_grouping_id)->first();
    }

    /**
     * @param int $table_id
     * @param array $data
     * @return Model|TableGroupingField
     */
    public function addTableGroupingField(int $table_id, array $data)
    {
        $data['field_name_visible'] = $data['field_name_visible'] ?? 1;
        $data['default_state'] = $data['default_state'] ?? 'expanded';
        $data['indent'] = $data['indent'] ?? 20;
        $data['row_order'] = 1;

        $grField = TableGroupingField::create(array_merge($data, ['grouping_id' => $table_id]));
        $grField->update(['row_order' => $grField->id]);

        return $grField;
    }

    /**
     * @param int $table_grouping_id
     * @param array $data
     * @return bool|int
     */
    public function updateTableGroupingField(int $table_grouping_id, array $data)
    {
        return TableGroupingField::where('id', '=', $table_grouping_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_grouping_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTableGroupingField($table_grouping_id)
    {
        return TableGroupingField::where('id', '=', $table_grouping_id)->delete();
    }

    /**
     * @param $table_grouping_id
     * @return TableGroupingStat|null
     */
    public function getTableGroupingStat($table_grouping_id)
    {
        return TableGroupingStat::where('id', '=', $table_grouping_id)->first();
    }

    /**
     * @param $table_grouping_id
     * @return TableGroupingFieldStat|null
     */
    public function getTableGroupingFieldStat($table_grouping_id)
    {
        return TableGroupingFieldStat::where('id', '=', $table_grouping_id)->first();
    }

    /**
     * @param int $parent_id
     * @param array $data
     * @param string $type
     * @return Model|TableGroupingFieldStat|TableGroupingStat
     */
    public function addTableGroupingStat(int $parent_id, array $data, string $type)
    {
        if ($type == 'global') {
            return TableGroupingStat::create(array_merge($data, ['grouping_id' => $parent_id]));
        } else {
            return TableGroupingFieldStat::create(array_merge($data, ['grouping_field_id' => $parent_id]));
        }
    }

    /**
     * @param int $stat_id
     * @param array $data
     * @param string $type
     * @return bool
     */
    public function updateTableGroupingStat(int $stat_id, array $data, string $type)
    {
        if ($type == 'global') {
            return TableGroupingStat::where('id', '=', $stat_id)
                ->update($this->service->delSystemFields($data));
        } else {
            return TableGroupingFieldStat::where('id', '=', $stat_id)
                ->update($this->service->delSystemFields($data));
        }
    }

    /**
     * @param $stat_id
     * @param string $type
     * @return bool|null
     * @throws Exception
     */
    public function deleteTableGroupingStat($stat_id, string $type)
    {
        if ($type == 'global') {
            return TableGroupingStat::where('id', '=', $stat_id)->delete();
        } else {
            return TableGroupingFieldStat::where('id', '=', $stat_id)->delete();
        }
    }

    /**
     * @param TableGrouping $grouping
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableGroupingRight
     */
    public function toggleGroupingRight(TableGrouping $grouping, int $table_permis_id, $can_edit)
    {
        $right = $grouping->_grouping_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableGroupingRight::create([
                'table_grouping_id' => $grouping->id,
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
     * @param TableGrouping $grouping
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteGroupingRight(TableGrouping $grouping, int $table_permis_id)
    {
        return $grouping->_grouping_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}