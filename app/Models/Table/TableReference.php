<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TableReference extends Model
{
    protected $table = 'table_references';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'table_field_id',
        'ref_table_id',
        'ref_field_id',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _ref_table() {
        return $this->hasOne(Table::class, 'id', 'ref_table_id');
    }

    public function _ref_field() {
        return $this->hasOne(TableField::class, 'id', 'ref_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
