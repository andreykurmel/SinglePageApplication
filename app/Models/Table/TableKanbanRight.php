<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_kanban_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableChart $_kanban
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableKanbanRight extends Model
{
    protected $table = 'table_kanban_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_kanban_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _kanban()
    {
        return $this->belongsTo(TableKanbanSettings::class, 'table_kanban_id', 'id');
    }
}
