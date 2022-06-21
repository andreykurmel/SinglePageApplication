<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class FormulaWatcher
{
    use RefCondWatchTrait {
        watch as protected traitWatch;
    }

    /**
     * @param int $table_id
     * @param int $lvl
     */
    public function watch(int $table_id, int $lvl = 0)
    {
        $this->traitWatch($table_id, $lvl);
    }


    /**
     * @param Table $ref_table
     * @param TableRefCondition $ref_cond
     * @return bool
     */
    protected function filterFunction(Table $ref_table, TableRefCondition $ref_cond)
    {
        $formula_fields = $ref_table->_fields->where('input_type', '=', 'Formula');
        if ($formula_fields->count()) {
            return !!(new TableDataQuery($ref_table))->hasReferencesInFormulas($ref_cond);
        }
        return false;
    }

    /**
     * @param Table $ref_table
     */
    protected function doAction(Table $ref_table)
    {
        $calc = new FormulaEvaluatorRepository($ref_table);
        $calc->recalcTableFormulas();
    }
}