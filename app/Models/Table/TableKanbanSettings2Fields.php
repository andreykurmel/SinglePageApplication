<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableKanbanSettings
 *
 * @property int $id
 * @property int $table_kanban_setting_id
 * @property int $table_field_id
 * @property int $is_header_show
 * @property int $is_header_value
 * @property int $table_show_name
 * @property int $table_show_value
 * @property string $picture_style
 * @property int $cell_border
 * @property string $picture_fit
 * @property int $is_start_table_popup
 * @property int $is_table_field_in_popup
 * @property int $is_hdr_lvl_one_row
 * @mixin Eloquent
 */
class TableKanbanSettings2Fields extends Model
{
    public $timestamps = false;

    protected $table = 'table_kanban_settings_2_table_fields';

    protected $fillable = [
        'table_kanban_setting_id',
        'table_field_id',
        'is_header_show',
        'is_header_value',
        'table_show_name',
        'table_show_value',
        'picture_style', //scroll,slide
        'cell_border',
        'picture_fit', //fill,height,width
        'width_of_table_popup',
        'is_start_table_popup',
        'is_table_field_in_popup',
        'is_hdr_lvl_one_row',
    ];
}
