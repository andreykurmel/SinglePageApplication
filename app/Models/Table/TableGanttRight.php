<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_gantt_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableGantt $_gantt
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableGanttRight extends Model
{
    protected $table = 'table_gantt_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_gantt_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _gantt()
    {
        return $this->belongsTo(TableGantt::class, 'table_gantt_id', 'id');
    }
}
