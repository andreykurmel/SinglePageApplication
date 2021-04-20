<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class FavoriteRow extends Model
{
    protected $table = 'favorite_rows';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'row_id',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
