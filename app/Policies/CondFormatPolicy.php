<?php

namespace Vanguard\Policies;

use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CondFormatPolicy
{
    use HandlesAuthorization;

    /**
     * User can do all actions if he is UserGroup owner
     *
     * @param User $user
     * @param CondFormat $condFormat
     * @return bool
     */
    public function isOwner(User $user, CondFormat $condFormat) {
        return $user->id && $condFormat->user_id == $user->id;
    }
}
