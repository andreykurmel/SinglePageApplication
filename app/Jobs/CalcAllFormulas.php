<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class CalcAllFormulas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * AutoFillAllDdls constructor.
     *
     * @param Table $table
     * @param $user_id
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Table::query()->whereHas('_fields', function ($q) {
            $q->where('input_type', '=', 'Formula');
        })->chunk(50, function ($tables) {
            $tables->load('_fields');
            foreach ($tables as $table) {
                foreach ($table->_fields as $field) {
                    if ($field->is_uniform_formula) {
                        (new TableDataQuery($table))->getQuery()->update([
                            $field->field . '_formula' => $field->f_formula,
                        ]);
                    }
                }
                $calc = new FormulaEvaluatorRepository($table);
                $calc->recalcTableFormulas();
            }
        });
    }

    public function failed()
    {
        //
    }
}
