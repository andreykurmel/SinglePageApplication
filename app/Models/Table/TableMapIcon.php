<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableMapIcon
 *
 * @property int $id
 * @property int $table_field_id
 * @property string $row_val
 * @property string $icon_path
 * @property int|null $height
 * @property int|null $width
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\User|null $_modified_user
 * @mixin \Eloquent
 */
class TableMapIcon extends Model
{
    protected $table = 'table_map_icons';

    public $timestamps = false;

    protected $fillable = [
        'table_field_id',
        'row_val',
        'icon_path',
        'height',
        'width',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
