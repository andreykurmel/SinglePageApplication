<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

class DDLItem extends Model
{
    protected $table = 'ddl_items';

    public $timestamps = false;

    protected $fillable = [
        'ddl_id',
        'apply_target_row_group_id',
        'option',
        'description',
        'image_path',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    /**
     * @param $q
     * @return mixed
     */
    public function scopeIsTbRef($q) {
        return $q->whereNotNull('image_path');
    }


    public function _ddl() {
        return $this->belongsTo(DDL::class, 'ddl_id', 'id');
    }

    public function _apply_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'apply_target_row_group_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
