<?php

namespace Vanguard\Policies;

use Vanguard\Models\Folder\Folder;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;

    /**
     * User can do all actions if he is folder owner
     *
     * @param User $user
     * @param \Vanguard\Models\Folder\Folder $folder
     * @return bool
     */
    public function isOwner(User $user, Folder $folder) {
        return
            $user->id && $folder->user_id == $user->id
            ||
            $user->isAdmin() && is_null($folder->user_id);
    }
}
