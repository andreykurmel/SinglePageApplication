<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_kanban_id
 * @property int $table_field_id
 * @property string $stat
 * @property-read TableChart $_kanban
 * @property-read TableField $_field
 * @mixin Eloquent
 */
class TableKanbanGroupParam extends Model
{
    protected $table = 'table_kanban_group_params';

    public $timestamps = false;

    protected $fillable = [
        'table_kanban_id',
        'table_field_id',
        'stat'
    ];


    public function _field()
    {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _kanban()
    {
        return $this->belongsTo(TableKanbanSettings::class, 'table_kanban_id', 'id');
    }
}
