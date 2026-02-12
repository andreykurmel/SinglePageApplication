<?php

namespace Vanguard\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Vanguard\Models\Pages\Pages;
use Vanguard\User;

class PagesPolicy
{
    use HandlesAuthorization;

    /**
     * TableDataPolicy constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param User $user
     * @param Pages $page
     * @return bool
     */
    public function isOwner(User $user, Pages $page)
    {
        return ($user->id && $page->user_id == $user->id)
            || (auth()->user() && auth()->user()->isAdmin()); //or Admin
    }
}
