<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;

/**
 * Vanguard\Models\Table\TableMap
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string $map_data_range
 * @property int $map_active
 * @property string|null $description
 * @property int|null $map_multiinfo
 * @property int|null $map_icon_field_id
 * @property string $map_icon_style
 * @property int|null $map_position_refid
 * @property int|null $map_popup_hdr_id
 * @property int $map_popup_width
 * @property int $map_popup_height
 * @property string $map_popup_header_color
 * @property string $map_picture_style
 * @property string $map_picture_position
 * @property int|null $map_picture_field
 * @property float $map_picture_width
 * @property float $map_picture_min_width
 * @property Table $_table
 * @property TableRefCondition $_ref_position
 * @property Collection|TableMapRight[] $_map_rights
 * @property Collection|TableMapFieldSetting[] $_map_field_settings
 * @mixin \Eloquent
 */
class TableMap extends Model
{
    protected $table = 'table_maps';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'map_data_range',
        'description',
        'map_active',

        'map_multiinfo',
        'map_icon_field_id',
        'map_icon_style',
        'map_position_refid',//RefCondition
        'map_popup_hdr_id',//RefCondition
        'map_popup_header_color',
        'map_popup_width',
        'map_popup_height',
        'map_picture_style',
        'map_picture_field',
        'map_picture_position',
        'map_picture_width',
        'map_picture_min_width',
    ];



    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _ref_position() {
        return $this->belongsTo(TableRefCondition::class, 'map_position_refid', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_map_rights', 'table_map_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_map_id', 'table_permission_id', 'can_edit']);
    }

    public function _map_rights() {
        return $this->hasMany(TableMapRight::class, 'table_map_id', 'id');
    }

    public function _map_field_settings() {
        return $this->hasMany(TableMapFieldSetting::class, 'table_map_id', 'id');
    }
}
