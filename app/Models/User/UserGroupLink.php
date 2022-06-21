<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserGroupLink
 *
 * @property int $id
 * @property int $user_group_id
 * @property int $user_id
 * @property int $cached_from_conditions
 * @property int $is_edit_added
 * @property-read \Vanguard\User $_user
 * @property-read \Vanguard\Models\User\UserGroup $_user_group
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink whereCachedFromConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink whereIsEditAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink whereUserGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupLink whereUserId($value)
 * @mixin \Eloquent
 */
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
