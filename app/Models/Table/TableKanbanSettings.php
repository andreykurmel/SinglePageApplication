<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableKanbanSettings
 *
 * @property int $id
 * @property int $table_field_id
 * @property int|null $kanban_group_field_id
 * @property string|null $columns_order
 * @property string|null $cards_order
 * @property int $kanban_active
 * @property int $kanban_form_table
 * @property int|null $kanban_center_align
 * @property int $kanban_card_width
 * @property int|null $kanban_card_height
 * @property string $kanban_header_color
 * @property string|null $kanban_sort_type
 * @property int $kanban_row_spacing
 * @property int|null $kanban_picture_field
 * @property int|null $kanban_picture_width
 * @property string $kanban_picture_position
 * @property int $kanban_hide_empty_tab
 * @property string $kanban_data_range
 * @property string|null $kanban_field_name
 * @property string|null $kanban_field_description
 * @property-read TableField[] $_columns
 * @property-read TableKanbanRight[] $_kanban_rights
 * @property-read TableKanbanGroupParam[] $_group_params
 * @property-read TableField $_field
 * @property-read TableKanbanSettings2Fields[] $_fields_pivot
 * @mixin Eloquent
 */
class TableKanbanSettings extends Model
{
    protected $table = 'table_kanban_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'table_field_id',
        'kanban_group_field_id',
        'kanban_field_name',
        'kanban_field_description',
        'columns_order',
        'cards_order',
        'kanban_active',
        'kanban_form_table',
        'kanban_center_align',
        'kanban_card_width',
        'kanban_card_height',
        'kanban_sort_type',
        'kanban_row_spacing',
        'kanban_header_color',
        'kanban_hide_empty_tab',
        'kanban_picture_field',
        'kanban_picture_width',
        'kanban_picture_position',
        'kanban_data_range',
    ];

    public static $forCopy = [
        'kanban_data_range',
        'kanban_form_table',
        'kanban_center_align',
        'kanban_row_spacing',
        'kanban_header_color',
        'kanban_card_width',
        'kanban_card_height',
        'kanban_sort_type',
        'kanban_hide_empty_tab',
        'kanban_picture_width',
        'kanban_picture_position',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _kanban_rights()
    {
        return $this->hasMany(TableKanbanRight::class, 'table_kanban_id', 'id');
    }

    public function _group_params()
    {
        return $this->hasMany(TableKanbanGroupParam::class, 'table_kanban_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_kanban_rights', 'table_kanban_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_kanban_id', 'table_permission_id', 'can_edit']);
    }

    public function _columns()
    {
        return $this->belongsToMany(TableField::class, 'table_kanban_settings_2_table_fields', 'table_kanban_setting_id', 'table_field_id');
    }

    public function _fields_pivot()
    {
        return $this->hasMany(TableKanbanSettings2Fields::class, 'table_kanban_setting_id', 'id');
    }
}
