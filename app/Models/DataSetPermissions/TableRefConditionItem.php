<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableRefConditionItem extends Model
{
    protected $table = 'table_ref_condition_items';

    public $timestamps = false;

    protected $fillable = [
        'table_ref_condition_id',
        'logic_operator',
        'table_field_id',
        'compared_operator',
        'compared_field_id',
        'compared_value',
        'spec_show',
        'item_type',
        'group_clause',
        'group_logic',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _ref_condition() {
        return $this->belongsTo(TableRefCondition::class, 'table_ref_condition_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }

    public function _compared_field() {
        return $this->hasOne(TableField::class, 'id', 'compared_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
