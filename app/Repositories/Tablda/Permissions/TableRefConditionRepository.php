<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Watchers\RefCondRenameWatcher;
use Vanguard\Services\Tablda\HelperService;

class TableRefConditionRepository
{
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
     * Get Referencing Condition with relations.
     *
     * @param $ref_cond_id
     * @return mixed
     */
    public function getRefCondWithRelations($ref_cond_id)
    {
        return TableRefCondition::where('id', '=', $ref_cond_id)
            ->with( $this->refConditionRelations() )
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
                '_items' => function($i) {
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
                '_items' => function($i) {
                    $i->with([
                        '_field',
                        '_compared_field'
                    ]);
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
     * @param $user_id
     * @return TableRefCondition
     */
    public function addRefCondition(array $data, Table $table, $user_id)
    {
        if (empty($data['ref_table_id'])) {
            $data['ref_table_id'] = $data['table_id'];
        }

        $tb_ref_cond = TableRefCondition::create(
            array_merge($this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated())
        );
        return $this->loadRefCondWithRelations($tb_ref_cond->id);
    }

    /**
     * @param int $ref_cond_id
     * @return TableRefCondition
     */
    public function loadRefCondWithRelations(int $ref_cond_id)
    {
        return TableRefCondition::where('id', $ref_cond_id)
            ->with([
                '_items',
                '_ref_table' => function ($q) {
                    $q->with([
                        '_fields' => function ($f) {
                            $f->joinOwnerHeader();
                        },
                    ]);
                }
            ])
            ->first();
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

        TableRefCondition::where('id', $group_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));

        return $this->loadRefCondWithRelations($group_id);
    }

    /**
     * Get Referencing Condition.
     *
     * @param $ref_cond_id
     * @return mixed
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
        return TableRefConditionItem::create(
            array_merge($this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated())
        );
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
        return TableRefConditionItem::where('id', $group_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * Delete Item of the Referencing Condition
     *
     * @param int $group_id
     * @return mixed
     */
    public function deleteRefConditionItem($group_id)
    {
        return TableRefConditionItem::where('id', $group_id)->delete();
    }
}