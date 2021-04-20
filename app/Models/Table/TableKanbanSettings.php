<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\User;

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
}
