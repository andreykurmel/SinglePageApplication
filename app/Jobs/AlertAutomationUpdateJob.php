<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Services\Tablda\AlertFunctionsService;

class AlertAutomationUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $alert_id;
    private $trigger_row;

    /**
     * AlertAutomationUpdateJob constructor.
     * @param int $alert_id
     * @param array $trigger_row
     */
    public function __construct(int $alert_id, array $trigger_row)
    {
        $this->alert_id = $alert_id;
        $this->trigger_row = $trigger_row;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new AlertFunctionsService())->automationUpdateRecordsJob($this->alert_id, $this->trigger_row);
    }

    public function failed()
    {
        //
    }
}
