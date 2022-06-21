<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Modules\Permissions\TableRights;

class RefConditionApplier
{
    /**
     * @var TableRefCondition
     */
    protected $ref_condition;

    /**
     * @param TableRefCondition $ref_condition
     */
    public function __construct(TableRefCondition $ref_condition)
    {
        $ref_condition->loadMissing('_items','_ref_table','_table');
        $ref_condition->_items->loadMissing('_compared_field', '_field');

        $this->ref_condition = $ref_condition;
    }

    /**
     * @param Builder $cond_query
     * @param array|null $present_row
     * @return Builder
     */
    public function applyToQuery(Builder $cond_query, array $present_row = null)
    {
        $this->checkThatCanApply();

        if ($this->ref_condition->_items && $this->ref_condition->_items->count()) {
            $grouped_conditions = $this->ref_condition->_items->groupBy('group_clause');
            $that = $this;
            $cond_query->where(function ($query) use ($that, $grouped_conditions, $present_row) {
                foreach ($grouped_conditions as $conditions) {

                    $lo_operator = 'AND';
                    foreach ($conditions as $cond) {
                        if ($cond->group_logic == 'OR') {
                            $lo_operator = 'OR';
                        }
                    }
                    $func = $lo_operator == 'OR' ? 'orWhere' : 'where';

                    $query->{$func}(function ($in_group) use ($that, $conditions, $present_row) {

                        foreach ($conditions as $item) {
                            if ($item->_compared_field) {
                                $item->setRelation('_ref_condition', $this->ref_condition);
                                $in_group = (new RefCondItemQueryApplier($item))->applyToQuery($in_group, $present_row);
                            }
                        }

                    });
                }
            });
        } else {
            $cond_query->whereRaw('true');
        }
        return $cond_query;
    }

    /**
     * @param Builder $cond_query
     * @param string $type
     * @return Builder
     */
    public function joinToQuery(Builder $cond_query, string $type = 'join')
    {
        $this->checkThatCanApply();

        $filters = $this->ref_condition->_items ? $this->ref_condition->_items->where('item_type', '=', 'P2S') : collect([]);
        if ($filters->count()) {
            $cond_query->{$type}($this->ref_condition->_ref_table->db_name, function ($join_query) use ($filters) {
                foreach ($filters as $flt) {
                    $func = $flt->logic_operator == 'OR' || $flt->group_logic == 'OR' ? 'orOn' : 'on';

                    $com_db_field = $flt->_compared_field ? $flt->_compared_field->field : 'id';
                    $com_db_field = SqlFieldHelper::getSqlFld($this->ref_condition->_ref_table, $com_db_field);

                    $compared_operator = $flt->compared_operator == 'Include' ? 'Like' : ($flt->compared_operator ?: '=');

                    $db_field = $flt->_field ? $flt->_field->field : 'id';
                    $db_field = SqlFieldHelper::getSqlFld($this->ref_condition->_table, $db_field);

                    $join_query->{$func}($db_field, $compared_operator, $com_db_field);
                }
            });
        }
        return $cond_query;
    }

    /**
     * @throws \Exception
     */
    protected function checkThatCanApply()
    {
        //is RefCondidition is referred to another table.
        if ($this->ref_condition->_table->user_id != $this->ref_condition->_ref_table->user_id)
        {
            $permission = TableRights::permissions($this->ref_condition->_ref_table);
            if (!$permission->is_owner && !$permission->referencing_shared) {
                throw new \Exception('Cannot apply RefCondtion: '.$this->ref_condition->name.' (needed "Owner" or "Permission:Referencing for Sharing")', 1);
            }
        }
    }
}