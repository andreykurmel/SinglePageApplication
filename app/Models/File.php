<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class File extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'table_field_id',
        'row_id',
        'filepath',
        'filename',
        'is_img',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table_info() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
