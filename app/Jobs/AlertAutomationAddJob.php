<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Services\Tablda\AlertFunctionsService;

class AlertAutomationAddJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $alert_id;

    /**
     * AlertAutomationUpdateJob constructor.
     * @param int $alert_id
     */
    public function __construct(int $alert_id)
    {
        $this->alert_id = $alert_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new AlertFunctionsService())->automationAddRecordsJob($this->alert_id);
    }

    public function failed()
    {
        //
    }
}
