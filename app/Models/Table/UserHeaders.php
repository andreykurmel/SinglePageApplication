<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

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
