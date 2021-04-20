<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\Table;
use Vanguard\Watchers\DDLWatcher;
use Vanguard\Watchers\FormulaWatcher;

class DDLChangedWatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $new_row;
    private $old_row;

    /**
     * DDLChangedWatcherJob constructor.
     * @param Table $table
     * @param array $new_row
     * @param array $old_row
     */
    public function __construct(Table $table, array $new_row, array $old_row)
    {
        $this->table = $table;
        $this->new_row = $new_row;
        $this->old_row = $old_row;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new DDLWatcher())->watch($this->table, $this->new_row, $this->old_row);
    }

    public function failed()
    {
        //
    }
}
