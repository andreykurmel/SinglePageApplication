<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\AlertFunctionsService;

class DelayAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $alert_id;
    private $active_rows;
    private $recipients;
    private $changed_fields;
    private $type;

    /**
     * DelayAlertJob constructor.
     * @param int $alert_id
     * @param array $active_rows
     * @param array $recipients
     * @param array $changed_fields
     * @param string $type
     */
    public function __construct(int $alert_id, array $active_rows, array $recipients, array $changed_fields, string $type)
    {
        $this->alert_id = $alert_id;
        $this->active_rows = $active_rows;
        $this->recipients = $recipients;
        $this->changed_fields = $changed_fields;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ids = array_pluck($this->active_rows, 'id');
        $alert = (new TableAlertsRepository())->getAlert($this->alert_id);
        $new_active_rows = (new TableDataQuery($alert->_table))
            ->getQuery()
            ->whereIn('id', $ids)
            ->get()
            ->toArray();

        //use saved rows for 'onDelete' handlers.
        if (!$new_active_rows && $alert->on_deleted) {
            $new_active_rows = $this->active_rows;
        }

        if ($new_active_rows) {
            (new AlertFunctionsService())->sendEmailNotificationjob(
                $this->alert_id,
                $new_active_rows,
                $this->recipients,
                $this->changed_fields,
                $this->type
            );
        }
    }

    public function failed()
    {
        //
    }
}
