<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableKanbanSettings
 *
 * @property int $id
 * @property int $table_field_id
 * @property int|null $kanban_group_field_id
 * @property string|null $columns_order
 * @property string|null $cards_order
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableField[] $_columns
 * @property-read int|null $_columns_count
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableKanbanSettings2Fields[] $_fields_pivot
 * @property-read int|null $_fields_pivot_count
 * @mixin \Eloquent
 */
class TableKanbanSettings extends Model
{
    protected $table = 'table_kanban_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_field_id',
        'kanban_group_field_id',
        'columns_order',
        'cards_order',
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _columns() {
        return $this->belongsToMany(TableField::class, 'table_kanban_settings_2_table_fields', 'table_kanban_setting_id', 'table_field_id');
    }

    public function _fields_pivot() {
        return $this->hasMany(TableKanbanSettings2Fields::class, 'table_kanban_setting_id', 'id');
    }
}
