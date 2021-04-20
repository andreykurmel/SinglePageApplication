<?php

namespace Vanguard\Policies;

use Vanguard\Models\AppTheme;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Table\Table;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppThemePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param AppTheme $appTheme
     * @return bool
     */
    public function edit(User $user, AppTheme $appTheme) {
        if ($appTheme->obj_type == 'system')
        {
            return $user->isAdmin();
        }
        elseif ($appTheme->obj_type == 'user')
        {
            $owner = User::where('id', $appTheme->obj_id)->first();
            return $owner && $owner->id == $user->id;
        }
        elseif ($appTheme->obj_type == 'table')
        {
            $table = Table::where('id', $appTheme->obj_id)->first();
            return $table && $table->user_id == $user->id;
        }
        else
        {
            return false;
        }
    }
}
