<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;

class RecalcTableFormula implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $import_id;
    private $user_id;
    private $changed_fields;

    /**
     * RecalcTableFormula constructor.
     *
     * @param Table $table
     * @param $user_id
     * @param $import_id
     */
    public function __construct(Table $table, $user_id, $import_id, $changed_fields = [])
    {
        $this->table = $table;
        $this->import_id = $import_id;
        $this->user_id = $user_id;
        $this->changed_fields = $changed_fields;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '1200');

        $evaluator = new FormulaEvaluatorRepository($this->table, $this->user_id, 0, $this->changed_fields);
        $evaluator->recalcTableFormulas([], $this->import_id);
    }

    public function failed()
    {
        //
    }
}
