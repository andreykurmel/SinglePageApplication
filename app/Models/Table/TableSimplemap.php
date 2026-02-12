<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableSimplemap
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $name
 * @property string $map
 * @property string $tb_smp_data_range
 * @property int $level_fld_id
 * @property string $multirec_style
 * @property int|null $multirec_fld_id
 * @property int $smp_active
 * @property string $smp_header_color
 * @property int $smp_card_width
 * @property int|null $smp_card_height
 * @property int|null $smp_card_max_height
 * @property int|null $smp_picture_field
 * @property int|null $smp_picture_width
 * @property int|null $smp_value_fld_id
 * @property int|null $smp_value_ddl_color
 * @property int|null $smp_color_fld_id
 * @property int|null $smp_theme_pop_link_id
 * @property string $smp_theme_pop_style
 * @property string $smp_picture_position
 * @property int|null $smp_on_hover_fld_id
 * @property int|null $smp_active_status_fld_id
 * @property int $smp_legend_size
 * @property string $smp_legend_orientation
 * @property int $smp_legend_pos_x
 * @property int $smp_legend_pos_y
 * @property int|null $locations_table_id
 * @property string $locations_data_range
 * @property int|null $locations_name_fld_id
 * @property int|null $locations_lat_fld_id
 * @property int|null $locations_long_fld_id
 * @property int|null $locations_descr_fld_id
 * @property string|null $locations_icon_shape_fld_id
 * @property int|null $locations_icon_color_fld_id
 * @mixin Eloquent
 */
class TableSimplemap extends Model
{
    public $timestamps = false;

    protected $table = 'table_simplemaps';

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'map',
        'level_fld_id',
        'multirec_style',
        'multirec_fld_id',
        'smp_active',
        'tb_smp_data_range',
        'smp_header_color',
        'smp_card_width',
        'smp_card_height',
        'smp_card_max_height',
        'smp_picture_field',
        'smp_picture_width',
        'smp_picture_position',
        'smp_value_fld_id',
        'smp_value_ddl_color',
        'smp_color_fld_id',
        'smp_theme_pop_link_id',
        'smp_theme_pop_style',
        'smp_active_status_fld_id',
        'smp_on_hover_fld_id',
        'smp_legend_size',
        'smp_legend_orientation',
        'smp_legend_pos_x',
        'smp_legend_pos_y',
        'locations_data_range',
        'locations_table_id',
        'locations_name_fld_id',
        'locations_lat_fld_id',
        'locations_long_fld_id',
        'locations_descr_fld_id',
        'locations_icon_shape_fld_id',
        'locations_icon_color_fld_id',
    ];

    public static $forCopy = [
        'map',
        'level_fld_id',
        'multirec_style',
        'multirec_fld_id',
        'smp_active',
        'tb_smp_data_range',
        'smp_header_color',
        'smp_card_width',
        'smp_card_height',
        'smp_card_max_height',
        'smp_picture_field',
        'smp_picture_width',
        'smp_picture_position',
        'smp_value_fld_id',
        'smp_value_ddl_color',
        'smp_color_fld_id',
        'smp_theme_pop_link_id',
        'smp_theme_pop_style',
        'smp_active_status_fld_id',
        'smp_on_hover_fld_id',
        'smp_legend_size',
        'smp_legend_orientation',
        'smp_legend_pos_x',
        'smp_legend_pos_y',
        'locations_data_range',
        'locations_table_id',
        'locations_name_fld_id',
        'locations_lat_fld_id',
        'locations_long_fld_id',
        'locations_descr_fld_id',
        'locations_icon_shape_fld_id',
        'locations_icon_color_fld_id',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_simplemap_rights', 'table_simplemap_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_simplemap_id', 'table_permission_id', 'can_edit']);
    }

    public function _simplemap_rights()
    {
        return $this->hasMany(TableSimplemapRight::class, 'table_simplemap_id', 'id');
    }

    public function _fields_pivot()
    {
        return $this->hasMany(TableSimplemaps2Fields::class, 'table_simplemap_id', 'id');
    }
}
