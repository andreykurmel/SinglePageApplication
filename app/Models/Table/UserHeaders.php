<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\UserHeaders
 *
 * @property int $id
 * @property int $table_field_id
 * @property int $user_id
 * @property string|null $unit_display
 * @property int $width
 * @property int $min_width
 * @property int $max_width
 * @property int $order
 * @property int $is_showed
 * @property string|null $notes
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int $filter
 * @property int $popup_header
 * @property int $is_floating
 * @property string $filter_type
 * @property int $show_zeros
 * @property string $col_align
 * @property int $show_history
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\User[] $_user
 * @property-read int|null $_user_count
 * @mixin \Eloquent
 */
class UserHeaders extends Model
{
    protected $table = 'user_headers';

    public $timestamps = false;

    public $avail_override_fields = [
        'filter',
        'filter_type', // ['value', 'range']
        'popup_header',
        'is_floating',
        'min_width',
        'max_width',
        'width',
        'unit_display',
        'col_align',
        'show_zeros',
        'show_history',
    ];

    protected $fillable = [
        'table_field_id',
        'user_id',

        'filter',
        'filter_type', // ['value', 'range']
        'popup_header',
        'is_floating',
        'min_width',
        'max_width',
        'width',
        'notes',
        'unit_display',
        'col_align',
        'show_zeros',
        'show_history',
        //always override
        'order',
        'is_showed',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _user() {
        return $this->hasMany(User::class, 'user_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
