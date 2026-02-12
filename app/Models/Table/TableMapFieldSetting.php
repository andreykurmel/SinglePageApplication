<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableMapFieldSetting
 *
 * @property int $id
 * @property int $table_map_id
 * @property int $table_field_id
 * @property int|null $is_lat_field
 * @property int|null $is_long_field
 * @property int|null $map_find_street_field
 * @property int|null $info_box
 * @property int|null $is_info_header_field
 * @property int|null $is_info_header_value
 * @property TableMap $_map
 * @property TableField $_field
 * @mixin \Eloquent
 */
class TableMapFieldSetting extends Model
{
    protected $table = 'table_map_field_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_map_id',
        'table_field_id',
        'is_lat_field',
        'is_long_field',
        'info_box',
        'is_info_header_field',
        'is_info_header_value',
    ];

    public static function booleans(): array
    {
        return array_merge(self::radios(), [
            'info_box',
            'is_info_header_field',
            'is_info_header_value',
        ]);
    }

    public static function radios(): array
    {
        return [
            'is_lat_field',
            'is_long_field',
        ];
    }



    public function _map() {
        return $this->belongsTo(TableMap::class, 'table_map_id', 'id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
