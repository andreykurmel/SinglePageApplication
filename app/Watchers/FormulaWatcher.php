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
     * @param Table $table
     */
    public function watch(Table $table)
    {
        $this->traitWatch($table);
    }


    /**
     * @param Table $ref_table
     * @param TableRefCondition $ref_cond
     * @return bool
     */
    protected function filterFunction(Table $ref_table, TableRefCondition $ref_cond)
    {
        return !!(new TableDataQuery($ref_table))->hasReferencesInFormulas($ref_cond);
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