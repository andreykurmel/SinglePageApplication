<?php

namespace Vanguard\Policies;

use Vanguard\Models\User\UserGroup;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserGroupPolicy
{
    use HandlesAuthorization;

    /**
     * User can do all actions if he is UserGroup owner
     *
     * @param User $user
     * @param UserGroup $userGroup
     * @return bool
     */
    public function isOwner(User $user, UserGroup $userGroup) {
        return $user->id && $userGroup->user_id == $user->id;
    }
}
