<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Watchers\FormulaWatcher;

class FormulaWatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $table_id;

    /**
     * FormulaWatcherJob constructor.
     * @param int $table_id
     */
    public function __construct(int $table_id)
    {
        $this->table_id = $table_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new FormulaWatcher())->watch($this->table_id);
    }

    public function failed()
    {
        //
    }
}
