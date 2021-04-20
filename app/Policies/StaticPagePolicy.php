<?php

namespace Vanguard\Policies;

use Vanguard\Models\Folder\Folder;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaticPagePolicy
{
    use HandlesAuthorization;

    /**
     * Only admin can change Static Pages.
     *
     * @param User $user
     * @return mixed
     */
    public function edit(User $user) {
        return $user->isAdmin() || $user->role_id == 3;
    }
}
