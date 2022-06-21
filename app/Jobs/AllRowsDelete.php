<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\AlertFunctionsService;
use Vanguard\Services\Tablda\TableDataService;

class AllRowsDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $table;
    protected $request;
    protected $user_id;

    /**
     * @param Table $table
     * @param array $request
     * @param int|null $user_id
     */
    public function __construct(Table $table, array $request, int $user_id = null)
    {
        $this->table = $table;
        $this->request = $request;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new TableDataService())->deleteAllRows($this->table, $this->request, $this->user_id);
    }

    public function failed()
    {
        //
    }
}
