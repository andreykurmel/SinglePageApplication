<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Modules\DdlShow\DdlShowModule;
use Vanguard\Modules\MirrorModule;
use Vanguard\Repositories\Tablda\TableRepository;

class WatchMirrorValues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $table_id;
    /**
     * @var array
     */
    protected $watchrow;

    /**
     * @param int $table_id
     * @param array $watchrow
     */
    public function __construct(int $table_id, array $watchrow = null)
    {
        $this->table_id = $table_id;
        $this->watchrow = $watchrow;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (!$this->table_id) {
            return;
        }

        $table = (new TableRepository())->getTable($this->table_id);
        $this->watchrow = (new DdlShowModule($table))->fillShows(null, $this->watchrow);

        if ($this->watchrow) {
            (new MirrorModule())->watch($this->table_id, $this->watchrow);
        } else {
            (new MirrorModule())->reFillAll($this->table_id);
        }
    }

    public function failed()
    {
        //
    }
}
