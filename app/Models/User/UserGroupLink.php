<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserGroupLink extends Model
{
    protected $table = 'user_groups_2_users';

    public $timestamps = false;

    protected $fillable = [
        'user_group_id',
        'user_id',
        'cached_from_conditions',
        'is_edit_added',
    ];


    public function _user_group() {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
