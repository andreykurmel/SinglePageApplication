<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class UserGroupCondition extends Model
{
    protected $table = 'user_group_conditions';

    public $timestamps = false;

    protected $fillable = [
        'user_group_id',
        'logic_operator',
        'user_field',
        'compared_operator',
        'compared_value',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user_group() {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
