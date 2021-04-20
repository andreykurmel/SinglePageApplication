<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class DDLReference extends Model
{
    protected $table = 'ddl_references';

    public $timestamps = false;

    protected $fillable = [
        'ddl_id',
        'table_ref_condition_id',
        'apply_target_row_group_id',
        'target_field_id',
        'sort_type',//[null,'asc','desc']
        'image_field_id',
        'show_field_id',
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
        return $q->where(function ($i) {
            //$i->whereNotNull('target_field_id');
            $i->whereNotNull('show_field_id');
            $i->orWhereNotNull('image_field_id');
        });
    }


    public function _ddl() {
        return $this->belongsTo(DDL::class, 'ddl_id', 'id');
    }

    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _apply_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'apply_target_row_group_id');
    }

    public function _target_field() {
        return $this->hasOne(TableField::class, 'id', 'target_field_id');
    }

    public function _image_field() {
        return $this->hasOne(TableField::class, 'id', 'image_field_id');
    }

    public function _show_field() {
        return $this->hasOne(TableField::class, 'id', 'show_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
