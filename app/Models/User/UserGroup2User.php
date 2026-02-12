<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\User\UserGroup2User
 *
 * @property int $id
 * @property int $user_group_id
 * @property int $user_id
 * @property int $cached_from_conditions
 * @property int $is_edit_added
 * @mixin \Eloquent
 */
class UserGroup2User extends Model
{
    protected $table = 'user_groups_2_users';

    public $timestamps = false;

    protected $fillable = [
        'user_group_id',
        'user_id',
        'cached_from_conditions',
        'is_edit_added',
    ];
}
