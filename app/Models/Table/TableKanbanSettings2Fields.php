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
 * @property int $table_show_name
 * @property string $picture_style
 * @property int $cell_border
 * @property string $picture_fit
 * @mixin Eloquent
 */
class TableKanbanSettings2Fields extends Model
{
    public $timestamps = false;
    protected $table = 'table_kanban_settings_2_table_fields';
    protected $fillable = [
        'table_kanban_setting_id',
        'table_field_id',
        'table_show_name',
        'picture_style', //scroll,slide
        'cell_border',
        'picture_fit', //fill,height,width
    ];
}
