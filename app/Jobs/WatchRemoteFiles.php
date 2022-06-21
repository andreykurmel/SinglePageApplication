<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Modules\MirrorModule;
//use Vanguard\Modules\RemoteFilesModule;

class WatchRemoteFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $table_id;
    /**
     * @var int
     */
    protected $field_id;
    /**
     * @var array
     */
    protected $watchrow;
    /**
     * @var bool
     */
    protected $remove;

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param array $watchrow
     */
    public function __construct(int $table_id, int $field_id = null, array $watchrow = [], bool $remove = false)
    {
        $this->table_id = $table_id;
        $this->field_id = $field_id;
        $this->watchrow = $watchrow;
        $this->remove = $remove;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (!$this->table_id) {
            return;
        }

        /*if ($this->watchrow && $this->field_id) {
            (new RemoteFilesModule())->watch($this->table_id, $this->watchrow);
        } elseif ($this->field_id) {
            (new RemoteFilesModule())->fill($this->table_id, $this->watchrow);
        } else {
            (new RemoteFilesModule())->reFillAll($this->table_id);
        }*/
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
