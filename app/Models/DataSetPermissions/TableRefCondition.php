<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class TableRefCondition extends Model
{
    protected $table = 'table_ref_conditions';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'ref_table_id',
        'name',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _ref_table() {
        return $this->hasOne(Table::class, 'id', 'ref_table_id');
    }

    public function _items() {
        return $this->hasMany(TableRefConditionItem::class, 'table_ref_condition_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
