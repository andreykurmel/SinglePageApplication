<?php

namespace Vanguard\Watchers;

use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class FormulaWatcher
{

    /**
     * @param int $table_id
     */
    public function watch(int $table_id)
    {
        $references = $this->getReferencedWithFormulas($table_id);
        foreach ($references as $ref_group) {
            $ref_table = $ref_group->first()->_table;
            $this->doAction($ref_table);
        }
    }

    /**
     * @param int $table_id
     */
    protected function getReferencedWithFormulas(int $table_id)
    {
        $references = TableRefCondition::where('ref_table_id', $table_id)
            ->where('table_id', '!=', $table_id)
            ->whereHas('_table', function ($t) {
                $t->whereHas('_fields', function ($f) {
                    $f->where('input_type', '=', 'Formula');
                    $f->where('is_uniform_formula', '=', 1);
                    $f->where(function ($inner) {
                        $inner->orWhere('f_formula', 'like', '%count(%');
                        $inner->orWhere('f_formula', 'like', '%countunique(%');
                        $inner->orWhere('f_formula', 'like', '%sum(%');
                        $inner->orWhere('f_formula', 'like', '%min(%');
                        $inner->orWhere('f_formula', 'like', '%max(%');
                        $inner->orWhere('f_formula', 'like', '%mean(%');
                        $inner->orWhere('f_formula', 'like', '%avg(%');
                        $inner->orWhere('f_formula', 'like', '%var(%');
                        $inner->orWhere('f_formula', 'like', '%std(%');
                    });
                });
            })
            ->with(['_table' => function ($q) {
                $q->select(['id', 'db_name', 'name', 'source', 'connection_id']);
                $q->with('_fields:id,table_id,field,input_type');
            }])
            ->get();
        return $references->groupBy('table_id');
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