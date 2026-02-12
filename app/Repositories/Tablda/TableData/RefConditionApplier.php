<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Modules\Permissions\TableRights;

class RefConditionApplier
{
    /**
     * @var TableRefCondition
     */
    protected $ref_condition;
    protected $subquery_data = [];

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
     * @param array $data
     * @return $this
     */
    public function setSubqueryData(array $data): self
    {
        $this->subquery_data = $data;
        return $this;
    }

    /**
     * @param Builder $cond_query
     * @param array|null $present_row
     * @return Builder
     */
    public function applyToQuery(Builder $cond_query, array $present_row = null)
    {
        $this->checkThatCanApply();

        if (!$present_row) {
            $present_row = request('special_params') ?: [];
            $present_row = $present_row['view_filtering_row'] ?? null;
        }

        $items = $this->getRefCondItems($this->ref_condition);
        if ($items && $items->count()) {
            $grouped_conditions = $items->groupBy('group_clause');
            $that = $this;
            $cond_query->where(function ($query) use ($that, $grouped_conditions, $present_row) {
                foreach ($grouped_conditions as $conditions) {

                    $first_cond = $conditions->first();
                    $grp_operator = $first_cond->group_logic == 'OR' ? 'OR' : 'AND';
                    $lo_operator = $first_cond->logic_operator == 'OR' ? 'OR' : 'AND';
                    $func = $grp_operator == 'OR' ? 'orWhere' : 'where';

                    $query->{$func}(function ($in_group) use ($that, $conditions, $present_row, $lo_operator) {

                        foreach ($conditions as $item) {
                            $item->setRelation('_ref_condition', $this->ref_condition);
                            $in_group = (new RefCondItemQueryApplier($in_group, $item, $lo_operator))
                                ->setSubqueryData($this->subquery_data)
                                ->applyToQuery($present_row);
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

        $filters = $this->getRefCondItems($this->ref_condition);
        if ($filters->count()) {
            $cond_query->{$type}($this->ref_condition->_ref_table->db_name, function ($join_query) use ($filters) {
                foreach ($filters as $flt) {
                    $func = $flt->logic_operator == 'OR' || $flt->group_logic == 'OR' ? 'orOn' : 'on';

                    $com_db_field = $flt->_compared_field ? $flt->_compared_field->field : 'id';
                    $com_db_field = SqlFieldHelper::getSqlFld($this->ref_condition->_ref_table, $com_db_field);

                    $compared_operator = $flt->compared_operator ?: '=';
                    switch (strtolower($compared_operator)) {
                        case 'include': $compared_operator = 'Like'; break;
                        case 'notinclude': $compared_operator = 'Not Like'; break;
                        case 'isempty': $compared_operator = '='; break;
                        case 'notempty': $compared_operator = '!='; break;
                    }

                    $db_field = $flt->_field ? $flt->_field->field : 'id';
                    $db_field = SqlFieldHelper::getSqlFld($this->ref_condition->_table, $flt->compared_value ?: $db_field);

                    $join_query->{$func}($db_field, $compared_operator, $com_db_field);
                }
            });
        }
        return $cond_query;
    }

    /**
     * @param TableRefCondition $mainRef
     * @param int $lvl
     * @return Collection
     */
    protected function getRefCondItems(TableRefCondition $mainRef, int $lvl = 0): Collection
    {
        $mainItems = $mainRef->_items ?: collect([]);

        $mainRef->load([
            '_base_refcond' => function ($r) {
                $r->with([
                    '_items' => function ($q) {
                        $q->with('_compared_field', '_field');
                    },
                    '_ref_table',
                    '_table',
                ]);
            },
        ]);

        if ($baseRef = $mainRef->_base_refcond) {
            $firstLogic = $mainItems->where('group_logic', '=', 'OR')->count() ? 'OR' : 'AND';
            $baseItems = $baseRef->_items ?: collect([]);
            foreach ($baseItems as $item) {
                $item->group_logic = $firstLogic;
                $item->group_clause .= $lvl;
            }
            $lvl += 1;
            $mainItems = $mainItems->merge($lvl < 3 ? $this->getRefCondItems($baseRef, $lvl) : $baseItems);
        }

        return $mainItems;
    }

    /**
     * @throws \Exception
     */
    protected function checkThatCanApply()
    {
        //is RefCondidition is referred to another table.
        if (false && $this->ref_condition->_table->user_id != $this->ref_condition->_ref_table->user_id)
        {
            $permission = TableRights::permissions($this->ref_condition->_ref_table);
            if (!$permission->is_owner && !$permission->can_reference) {
                throw new \Exception('Cannot apply RC: '.$this->ref_condition->name.' ("Permission: Referencing for Sharing" is required.)', 1);
            }
        }
    }
}