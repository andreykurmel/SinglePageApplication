<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TableSharedName extends Model
{
    protected $table = 'table_shared_names';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'name'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
