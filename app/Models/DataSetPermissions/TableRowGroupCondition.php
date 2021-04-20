<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableRowGroupCondition extends Model
{
    protected $table = 'table_row_group_conditions';

    public $timestamps = false;

    protected $fillable = [
        'table_row_group_id',
        'logic_operator',
        'table_field_id',
        'compared_operator',
        'compared_value',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _row_group() {
        return $this->belongsTo(TableRowGroup::class, 'table_row_group_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
