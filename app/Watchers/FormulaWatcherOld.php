<?php

namespace Vanguard\Watchers;

use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class FormulaWatcherOld
{
    private $calculated_tables = [];

    /**
     * @param Table $table
     */
    public function watch(Table $table)
    {
        $tables = Table::whereNotIn('id', $this->calculated_tables)
            ->whereHas('_ref_conditions', function ($rq) use ($table) {
                $rq->where('ref_table_id', $table->id);
                $rq->where('table_id', '!=', $table->id);
            })
            ->whereHas('_fields', function ($rq) use ($table) {
                $rq->where('input_type', 'Formula');
            })
            ->with('_fields')
            ->get();

        $ref_ids = $tables->pluck('id')->toArray();
        $this->calculated_tables = array_merge($this->calculated_tables, $ref_ids);

        $this->recalsSelectedReferences($tables);
    }

    /**
     * @param Collection $tables
     */
    private function recalsSelectedReferences(Collection $tables)
    {
        //recalc references
        foreach ($tables as $ref_table) {
            $calc = new FormulaEvaluatorRepository($ref_table);
            $calc->recalcTableFormulas();

            $this->watch($ref_table);
        }
    }
}