<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\User\UserGroupSubgroup
 *
 * @property int $id
 * @property int $usergroup_id
 * @property int $subgroup_id
 * @mixin \Eloquent
 */
class UserGroupSubgroup extends Model
{
    protected $table = 'user_group_subgroups';

    public $timestamps = false;

    protected $fillable = [
        'usergroup_id',
        'subgroup_id',
    ];


    public function _usergroup() {
        return $this->belongsTo(UserGroup::class, 'usergroup_id', 'id');
    }
}
