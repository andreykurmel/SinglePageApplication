<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class Communication extends Model
{
    protected $table = 'table_communications';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'from_user_id',
        'to_user_id',
        'to_user_group_id',
        'date',
        'message',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _from_user() {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function _to_user() {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

    public function _to_user_group() {
        return $this->belongsTo(UserGroup::class, 'to_user_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
