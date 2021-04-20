<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\Table;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class RecalcTableFormula implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $repository;
    private $import_id;
    private $user_id;

    /**
     * RecalcTableFormula constructor.
     *
     * @param Table $table
     * @param $user_id
     * @param $import_id
     */
    public function __construct(Table $table, $user_id, $import_id)
    {
        $this->table = $table;
        $this->repository = app()->make(TableDataService::class);
        $this->import_id = $import_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->repository->recalcTableFormulas($this->table, $this->user_id, [], $this->import_id);
    }

    public function failed()
    {
        //
    }
}
