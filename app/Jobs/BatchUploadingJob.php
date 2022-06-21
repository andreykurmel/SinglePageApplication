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

class BatchUploadingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $job_id;
    protected $table;
    protected $url_field_id;
    protected $attach_field_id;
    protected $row_group_id;

    /**
     * @param int $job_id
     * @param Table $table
     * @param int $url_field_id
     * @param int $attach_field_id
     * @param int|null $row_group_id
     */
    public function __construct(int $job_id, Table $table, int $url_field_id, int $attach_field_id, int $row_group_id = null)
    {
        $this->job_id = $job_id;
        $this->table = $table;
        $this->url_field_id = $url_field_id;
        $this->attach_field_id = $attach_field_id;
        $this->row_group_id = $row_group_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new TableDataService())->batchUploading($this->job_id, $this->table, $this->url_field_id, $this->attach_field_id, $this->row_group_id);
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
