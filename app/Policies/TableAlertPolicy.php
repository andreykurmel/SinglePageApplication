<?php

namespace Vanguard\Policies;

use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableView;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TableAlertPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param TableAlert $tableAlert
     * @return bool
     */
    public function owner(User $user, TableAlert $tableAlert) {
        return $tableAlert->_table->user_id == $user->id;
    }

    /**
     * @param User $user
     * @param TableAlert $tableAlert
     * @return bool
     */
    public function view(User $user, TableAlert $tableAlert) {
        return $tableAlert->_table->user_id == $user->id
            || $tableAlert->_table_permissions->count();
    }

    /**
     * @param User $user
     * @param TableAlert $tableAlert
     * @return bool
     */
    public function edit(User $user, TableAlert $tableAlert) {
        return $tableAlert->_table->user_id == $user->id
            || $tableAlert->_table_permissions->where('_right.can_edit', '=', 1)->count();
    }
}
