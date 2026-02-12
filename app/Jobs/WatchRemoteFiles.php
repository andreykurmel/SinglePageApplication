<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Modules\RemoteFilesModule;

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
    protected $newrow;
    /**
     * @var array
     */
    protected $oldrow;
    /**
     * @var bool
     */
    protected $remove;

    /**
     * @param int $table_id
     * @param int|null $field_id
     * @param array $newrow
     * @param array $oldrow
     * @param bool $remove
     */
    public function __construct(int $table_id, int $field_id = null, array $newrow = [], array $oldrow = [], bool $remove = false)
    {
        $this->table_id = $table_id;
        $this->field_id = $field_id;
        $this->newrow = $newrow;
        $this->oldrow = $oldrow;
        $this->remove = $remove;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (!$this->table_id) {
            return '';
        }

        $remote = new RemoteFilesModule($this->table_id);
        if ($remote->hasRemotes()) {

            if ($this->remove) {
                $remote->clear($this->field_id, $this->newrow['id'] ?? null);
            } else {
                if ($this->newrow) {
                    $remote->fillRow($this->newrow, $this->oldrow);
                } else {
                    $remote->fill($this->field_id);
                }
            }

        }
        return 'done';

    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
