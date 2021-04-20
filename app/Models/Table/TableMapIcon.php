<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

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
