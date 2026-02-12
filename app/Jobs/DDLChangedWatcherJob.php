<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Watchers\DDLWatcher;
use Vanguard\Watchers\FormulaWatcher;

class DDLChangedWatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table_id;
    private $new_row;
    private $old_row;

    /**
     * @param int $table_id
     * @param array $new_row
     * @param array $old_row
     */
    public function __construct(int $table_id, array $new_row, array $old_row)
    {
        $this->table_id = $table_id;
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
        $table = (new TableRepository())->getTable($this->table_id);
        (new DDLWatcher())->watch($table, $this->new_row, $this->old_row);
    }

    public function failed()
    {
        //
    }
}
