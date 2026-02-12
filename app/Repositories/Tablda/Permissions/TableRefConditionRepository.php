<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Jobs\WatchMirrorValues;
use Vanguard\Models\DataSetPermissions\TableRcmapPosition;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Support\DirectDatabase;
use Vanguard\Watchers\RefCondRenameWatcher;

class TableRefConditionRepository
{
    /**
     * @var HelperService
     */
    protected $service;

    /**
     * UserGroupRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table)
    {
        $table->load([
            '_ref_conditions' => function ($q) {
                $q->with([
                    '_ref_table' => function ($rt) { // optimise DB requests for getting '_src_table_link' below
                        $rt->with(TableRefConditionRepository::refCondRelations());
                    },
                    '_items' => function ($i) {
                        $i->with('_field', '_compared_field');
                    }
                ]);
                $q->orderBy('is_system', 'desc');
                $q->orderBy('row_order');
            },
            '_rcmap_positions',
        ]);
    }

    /**
     * @param array $table_ids
     * @param $oldValue
     * @return \Illuminate\Support\Collection
     */
    public function findRefsToSync(array $table_ids, $oldValue)
    {
        return TableRefConditionItem::query()
            ->whereHas('_ref_condition', function ($ref) use ($table_ids) {
                $ref->whereIn('table_id', $table_ids);
            })
            ->where('item_type', '=', 'S2V')
            ->where('compared_value', '=', $oldValue)
            ->get();
    }

    /**
     * @param Table $table
     * @param int $field_id
     * @return void
     */
    public function checkAndMakeSysRCforUserField(Table $table, int $field_id): void
    {
        $userRC = $this->getSysRefCond($table->id, 'User');
        if ($userRC) {
            $this->updateRefCondition($userRC->id, ['name' => 'User', 'is_system' => 1]);
        } else {
            $userRC = $this->addRefCondition(['name' => 'User', 'is_system' => 1], $table);
        }
        $userRC->_items()->delete();
        $this->addRefConditionItem([
            'table_ref_condition_id' => $userRC->id,
            'compared_field_id' => $field_id,
            'item_type' => 'S2V',
            'compared_value' => '{$user}',
            'compared_operator' => '=',
        ]);

        $groupRC = $this->getSysRefCond($table->id, 'UserGroup');
        if ($groupRC) {
            $this->updateRefCondition($groupRC->id, ['name' => 'UserGroup', 'is_system' => 1]);
        } else {
            $groupRC = $this->addRefCondition(['name' => 'UserGroup', 'is_system' => 1], $table);
        }
        $groupRC->_items()->delete();
        $this->addRefConditionItem([
            'table_ref_condition_id' => $groupRC->id,
            'compared_field_id' => $field_id,
            'item_type' => 'S2V',
            'compared_value' => '{$group}',
            'compared_operator' => '=',
        ]);
    }

    /**
     * @param int $table_id
     * @param string $by
     * @return \Illuminate\Database\Eloquent\Model|object|TableRefCondition|null
     */
    public function getSysRefCond(int $table_id, string $by = 'User')
    {

        $sql = TableRefCondition::where('table_id', '=', $table_id)
            ->where('is_system', '=', 1)
            ->where('name', '=', $by)
            ->orderBy('id');

        $rc = (clone $sql)->first();

        if ((clone $sql)->count() > 1) {
            (clone $sql)->where('id', '!=', $rc->id)->delete();
        }

        return $rc;
    }

    /**
     * Get Referencing Condition with relations.
     *
     * @param $ref_cond_id
     * @return TableRefCondition
     */
    public function getRefCondWithRelations($ref_cond_id)
    {
        return TableRefCondition::where('id', '=', $ref_cond_id)
            ->with($this->refConditionRelations())
            ->first();
    }

    /**
     * @param bool $small
     * @return array
     */
    public function refConditionRelations(bool $small = false)
    {
        if ($small) {
            return [
                '_table:id,db_name,user_id,source,is_system,connection_id',
                '_ref_table:id,db_name,user_id,source,is_system,connection_id',
                '_items' => function ($i) {
                    $i->with([
                        '_field:id,table_id,field,f_type',
                        '_compared_field:id,table_id,field,f_type,input_type'
                    ]);
                }
            ];
        } else {
            return [
                '_table',
                '_ref_table',
                '_items' => function ($i) {
                    $i->with('_field', '_compared_field');
                }
            ];
        }
    }

    /**
     * Get Referencing Condition By Item ID.
     *
     * @param $condition_item_id
     * @return mixed
     */
    public function getRefConditionByItemId($condition_item_id)
    {
        $tb_ref_cond = TableRefConditionItem::where('id', '=', $condition_item_id)->first();
        return TableRefCondition::where('id', '=', $tb_ref_cond->table_ref_condition_id)->first();
    }

    /**
     * Add Referencing Condition
     *
     * @param array $data
     * [
     *  +table_id: int,
     *  +ref_table_id: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @param Table $table
     * @return TableRefCondition
     */
    public function addRefCondition(array $data, Table $table)
    {
        if (empty($data['table_id'])) {
            $data['table_id'] = $table->id;
        }
        if (empty($data['ref_table_id'])) {
            $data['ref_table_id'] = $data['table_id'];
        }
        $data['row_order'] = TableRefCondition::query()->max('id') + 1;

        $tb_ref_cond = TableRefCondition::create(
            array_merge($this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated())
        );
        $this->autoAddP2S($tb_ref_cond);
        $this->removeUnchangedMapPositionsToKeepLayout($tb_ref_cond);
        return $this->loadRefCondWithRelations($tb_ref_cond->id);
    }

    /**
     * @param TableRefCondition $refCondition
     * @return void
     */
    public function autoAddP2S(TableRefCondition $refCondition)
    {
        if ($refCondition->table_id != $refCondition->ref_table_id) {
            foreach ($refCondition->_table->_fields->whereNotIn('field', $this->service->system_fields) as $field) {
                $refFld = $refCondition->_ref_table->_fields->where('name', '=', $field->name)->first();
                if ($refFld) {
                    $this->addRefConditionItem([
                        'table_ref_condition_id' => $refCondition->id,
                        'table_field_id' => $field->id,
                        'item_type' => 'P2S',
                        'compared_field_id' => $refFld->id,
                        'compared_operator' => '=',
                    ]);
                }
            }
        }
    }

    /**
     * @param int|array $ref_cond_id
     * @return TableRefCondition|Collection
     */
    public function loadRefCondWithRelations($ref_cond_id)
    {
        if (is_array($ref_cond_id)) {
            $sql = TableRefCondition::whereIn('id', $ref_cond_id);
        } else {
            $sql = TableRefCondition::where('id', '=', $ref_cond_id);
        }
        $sql->with([
            '_items' => function ($i) {
                $i->with('_field', '_compared_field');
            },
            '_ref_table' => function ($q) {
                $q->with(self::refCondRelations());
            }
        ]);
        return is_array($ref_cond_id) ? $sql->get() : $sql->first();
    }

    /**
     * @return \Closure[]
     */
    public static function refCondRelations(): array
    {
        return [
            '_link_initial_folder' => function ($lif) {
                $lif->with([
                    '_folder' => function ($f) {
                        $f->with('_root_folders');
                    }
                ]);
            },
            '_fields' => function ($f) {
                $f->joinOwnerHeader();
                $f->select(['id','table_id','field','name','input_type','ddl_id','f_type','formula_symbol']);
            },
            '_bi_charts:id,table_id,name,title',
        ];
    }

    /**
     * Update Referencing Condition
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_id: int,
     *  -ref_table_id: int,
     *  -name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function updateRefCondition($group_id, $data)
    {
        $old = $this->getRefCondition($group_id);
        if (!empty($data['name']) && $old->name != $data['name']) {
            (new RefCondRenameWatcher())->watchRename($old, $data['name']);
        }
        if (!empty($data['ref_table_id']) && $old->ref_table_id != $data['ref_table_id']) {
            (new DDLRepository())->syncRefCond($old->id);
            (new TableFieldLinkRepository())->syncRefCond($old->id);
            (new TableRowGroupRepository())->syncRefCond($old->id);
            TableRefConditionItem::where('table_ref_condition_id', '=', $old->id)->update([
                'table_field_id' => null,
                'compared_field_id' => null,
            ]);
        }
        if ($data['rc_static'] ?? 0) {// 'P2S' items are not available to STATIC RefConds
            TableRefConditionItem::query()
                ->where('table_ref_condition_id', '=', $old->id)
                ->where('item_type', '=', 'P2S')
                ->delete();
        }

        TableRefCondition::where('id', '=', $group_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));

        dispatch(new WatchMirrorValues($old->table_id));

        $this->removeUnchangedMapPositionsToKeepLayout($this->getRefCondition($group_id));
        return $this->loadRefCondWithRelations($group_id);
    }

    /**
     * Get Referencing Condition.
     *
     * @param $ref_cond_id
     * @return TableRefCondition
     */
    public function getRefCondition($ref_cond_id)
    {
        return TableRefCondition::where('id', '=', $ref_cond_id)->first();
    }

    /**
     * Delete Referencing Condition
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRefCondition($group_id)
    {
        TableField::where('mirror_rc_id', '=', $group_id)->update([
            'mirror_rc_id' => null,
            'mirror_field_id' => null,
            'mirror_part' => 'show',
        ]);

        return TableRefCondition::where('id', $group_id)->delete();
    }

    /**
     * Copy Referencing Condition
     *
     * @param TableRefCondition $from_ref
     * @param TableRefCondition $to_ref
     * @return mixed
     */
    public function copyRefCondition(TableRefCondition $from_ref, TableRefCondition $to_ref)
    {
        $items = $from_ref->_items()->get();
        foreach ($items as $item) {
            TableRefConditionItem::create(
                array_merge($this->service->delSystemFields($item->toArray()),
                    ['table_ref_condition_id' => $to_ref->id],
                    $this->service->getModified(),
                    $this->service->getCreated())
            );
        }
        return $this->loadRefCondWithRelations($to_ref->id);
    }

    /**
     * @param TableRefCondition $refCond
     * @return Collection|TableRefCondition
     */
    public function addReverseRefCond(TableRefCondition $refCond)
    {
        $reversed = TableRefCondition::create(array_merge(
            $this->service->delSystemFields($refCond->toArray()),
            [
                'ref_table_id' => $refCond->table_id,
                'table_id' => $refCond->ref_table_id,
                'name' => 'R_' . $refCond->name,
            ],
            $this->service->getModified(),
            $this->service->getCreated())
        );

        $items = $refCond->_items()->get();
        foreach ($items as $item) {
            TableRefConditionItem::create(
                array_merge($this->service->delSystemFields($item->toArray()),
                    [
                        'table_ref_condition_id' => $reversed->id,
                        'compared_field_id' => $item->table_field_id,
                        'compared_part' => $item->field_part,
                        'table_field_id' => $item->compared_field_id,
                        'field_part' => $item->compared_part,
                    ],
                    $this->service->getModified(),
                    $this->service->getCreated())
            );
        }

        return $this->loadRefCondWithRelations($reversed->id);
    }

    /**
     * Add Item of the Referencing Condition
     *
     * @param $data
     * [
     *  +table_ref_condition_id: int,
     *  +table_field_id: int,
     *  -compared_operator: string,
     *  -compared_field_id: string,
     *  -compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return mixed
     */
    public function addRefConditionItem($data)
    {
        $data['field_part'] = $data['field_part'] ?? 'value';
        $data['compared_part'] = $data['compared_part'] ?? 'value';

        $rc_item = TableRefConditionItem::create(
            array_merge($this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated())
        );

        $rc = $this->getRefCondition($data['table_ref_condition_id']);
        dispatch(new WatchMirrorValues($rc->table_id));

        return $rc_item;
    }

    /**
     * Update Item of the Referencing Condition
     *
     * @param int $group_id
     * @param $data
     * [
     *  -table_ref_condition_id: int,
     *  -table_field_id: int,
     *  -compared_operator: string,
     *  -compared_field_id: string,
     *  -compared_value: string,
     *  -logic_operator: string,
     * ]
     * @return mixed
     */
    public function updateRefConditionItem($group_id, $data)
    {
        $result = TableRefConditionItem::where('id', '=', $group_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));

        $rc_item = TableRefConditionItem::where('id', '=', $group_id)->first();
        $rc = $this->getRefCondition($rc_item->table_ref_condition_id);
        (new WatchMirrorValues($rc->table_id))->handle();

        if (($data['_changed_field'] ?? '') == 'group_logic') {
            TableRefConditionItem::where('table_ref_condition_id', '=', $rc_item->table_ref_condition_id)
                ->update(array_merge(['group_logic' => $rc_item->group_logic], $this->service->getModified()));
        }
        if (($data['_changed_field'] ?? '') == 'logic_operator') {
            TableRefConditionItem::where('table_ref_condition_id', '=', $rc_item->table_ref_condition_id)
                ->where('group_clause', '=', $rc_item->group_clause)
                ->update(array_merge(['logic_operator' => $rc_item->logic_operator], $this->service->getModified()));
        }

        return $result;
    }

    /**
     * Delete Item of the Referencing Condition
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRefConditionItem($group_id)
    {
        $rc_item = TableRefConditionItem::where('id', '=', $group_id)->first();
        $result = TableRefConditionItem::where('id', $group_id)->delete();

        $rc = $this->getRefCondition($rc_item->table_ref_condition_id);
        dispatch(new WatchMirrorValues($rc->table_id));

        return $result;
    }

    /**
     * @param int $master_table_id
     * @return array
     */
    public function loadIncomingRefConds(int $master_table_id): array
    {
        return DirectDatabase::loadIncomingLinks($master_table_id);
    }

    /**
     * @param array $position
     * @return TableRcmapPosition
     */
    public function updateOrCreateRcMaps(array $position): TableRcmapPosition
    {
        return TableRcmapPosition::updateOrCreate([
            'table_id' => $position['table_id'],
            'user_id' => $position['user_id'],
            'object_type' => $position['object_type'],
            'object_id' => $position['object_id'],
        ], $position);
    }

    /**
     * @param int $table_id
     * @param string $position
     * @return Collection|TableRcmapPosition[]
     */
    public function deleteRcMaps(int $table_id, string $position)
    {
        $allRefCondIds = $this->mapPositionQueryFor($table_id, 'ref_cond')->get(['object_id'])->pluck('object_id');
        $otherRefCondIds = TableRefCondition::whereIn('id', $allRefCondIds)->whereRaw('table_id != ref_table_id')->get(['id'])->pluck('id');

        switch ($position) {
            case 'left':
                $this->mapPositionQueryFor($table_id, 'table')->whereRaw('table_id != object_id')->delete();
                break;
            case 'center':
                $this->mapPositionQueryFor($table_id, 'ref_cond')->whereIn('object_id', $otherRefCondIds)->delete();
                break;
            case 'right':
                $this->mapPositionQueryFor($table_id, 'ref_cond')->whereNotIn('object_id', $otherRefCondIds)->delete();
                $this->mapPositionQueryFor($table_id, 'table')->whereRaw('table_id = object_id')->delete();
                break;
        }

        return TableRcmapPosition::where('table_id', '=', $table_id)->get();
    }

    /**
     * @param TableRefCondition $refCond
     * @return void
     */
    protected function removeUnchangedMapPositionsToKeepLayout(TableRefCondition $refCond): void
    {
        $allRefCondIds = $this->mapPositionQueryFor($refCond->table_id, 'ref_cond')->get(['object_id'])->pluck('object_id');
        $otherRefCondIds = TableRefCondition::whereIn('id', $allRefCondIds)->whereRaw('table_id != ref_table_id')->get(['id'])->pluck('id');

        if ($refCond->table_id != $refCond->ref_table_id) {
            $hasChanged = $this->mapPositionQueryFor($refCond->table_id, 'table')
                ->whereRaw('table_id != object_id')
                ->where('changed', '=', 1)
                ->count();
            if (! $hasChanged) {
                $this->mapPositionQueryFor($refCond->table_id, 'table')->whereRaw('table_id != object_id')->delete();
            }

            $hasChanged = $this->mapPositionQueryFor($refCond->table_id, 'ref_cond')
                ->whereIn('object_id', $otherRefCondIds)
                ->where('changed', '=', 1)
                ->count();
            if (! $hasChanged) {
                $this->mapPositionQueryFor($refCond->table_id, 'ref_cond')->whereIn('object_id', $otherRefCondIds)->delete();
            }
        } else {
            $hasChanged = $this->mapPositionQueryFor($refCond->table_id, 'ref_cond')
                ->whereNotIn('object_id', $otherRefCondIds)
                ->where('changed', '=', 1)
                ->count();
            if (! $hasChanged) {
                $this->mapPositionQueryFor($refCond->table_id, 'ref_cond')->whereNotIn('object_id', $otherRefCondIds)->delete();
            }
        }
    }

    /**
     * @param int $table_id
     * @param string $type
     * @return Builder
     */
    protected function mapPositionQueryFor(int $table_id, string $type): Builder
    {
        return TableRcmapPosition::query()
            ->where('table_id', '=', $table_id)
            ->where('user_id', '=', auth()->id())
            ->where('object_type', '=', $type);
    }
}