<?php

namespace Vanguard\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Modules\MirrorModule;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;

class FillMirrorValues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var TableField
     */
    protected $field;

    /**
     * @param int $table_field_id
     */
    public function __construct(int $table_field_id)
    {
        $this->field = (new TableFieldRepository())->getField($table_field_id);
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if (!$this->field || !$this->field->_mirror_rc || !$this->field->_mirror_field) {
            return;
        }
        (new MirrorModule())->fill($this->field);
    }

    public function failed()
    {
        //
    }
}
