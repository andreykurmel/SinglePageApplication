<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Singletones\AuthUserSingleton;

class RefConditionApplier
{
    private $ref_condition;

    /**
     * RefConditionApplier constructor.
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

        if ($this->ref_condition->_items) {
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
        }
        return $cond_query;
    }

    /**
     * @throws \Exception
     */
    protected function checkThatCanApply()
    {
        //is RefCond from table to another table AND RefTable is shared.
        if (
            $this->ref_condition->_table->user_id != $this->ref_condition->_ref_table->user_id
            &&
            $this->ref_condition->_ref_table->user_id != auth()->id()
        ) {

            //TODO: check that $is_shared is needed
            /*$shared_tables_ids = app( AuthUserSingleton::class )
                ->sharedTablesIds('all')
                ->toArray();*/
            $repo = new TablePermissionRepository();

            //owner of root table
            if ($this->ref_condition->_table->user_id == auth()->id()) {
                //ref table shared AND permission for ref table has 'can reference'
                //$is_shared = in_array($this->ref_condition->ref_table_id, $shared_tables_ids);
                $can_reference = $repo->canReference($this->ref_condition->ref_table_id);
            }
            //not owner for both tables
            else {
                //target table shared AND permission for ref table for target table user has 'can reference' and 'referencing shared'
                //$is_shared = in_array($this->ref_condition->table_id, $shared_tables_ids);
                $can_reference = $repo->canReference($this->ref_condition->ref_table_id, $this->ref_condition->_table->user_id);
            }


            if (/*!$is_shared || */!$can_reference) {
                throw new \Exception('Cannot apply RefCondtion: '.$this->ref_condition->name, 1);
            }
        }
    }
}