<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\Services\Tablda\TableDataService;

class DelayAlertExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $alert_id;
    private $table_id;
    private $type;
    private $all_rows_arr;
    private $changed_fields;

    /**
     * @param int $alert_id
     * @param int $table_id
     * @param string $type
     * @param array $all_rows_arr
     * @param array $changed_fields
     */
    public function __construct(int $alert_id, int $table_id, string $type, array $all_rows_arr = [], array $changed_fields = [])
    {
        $this->alert_id = $alert_id;
        $this->table_id = $table_id;
        $this->type = $type;
        $this->all_rows_arr = $all_rows_arr;
        $this->changed_fields = $changed_fields;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $alert = (new TableAlertsRepository())->getAlert($this->alert_id);
        $table = (new TableRepository())->getTable($this->table_id);

        $rows = (new TableDataService())->getRows([
            'table_id' => $table->id,
            'row_id' => Arr::pluck($this->all_rows_arr, 'id'),
            'page' => 1,
            'rows_per_page' => 1
        ], $table->user_id);

        $all_rows_arr = ($rows['rows'] ?? []) ?: $this->all_rows_arr;

        (new TableAlertService())->activateAlert($alert, $table, $this->type, $all_rows_arr, $this->changed_fields);
    }

    public function failed()
    {
        //
    }
}
