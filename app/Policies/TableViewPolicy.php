<?php

namespace Vanguard\Policies;

use Vanguard\Models\Table\TableView;
use Vanguard\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TableViewPolicy
{
    use HandlesAuthorization;

    /**
     * User can do all actions if he is table view owner
     *
     * @param User $user
     * @param \Vanguard\Models\Table\TableView $tableView
     * @return bool
     */
    public function isOwner(User $user, TableView $tableView) {
        return $tableView->user_id == $user->id;
    }
}
