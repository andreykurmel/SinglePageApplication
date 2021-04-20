<?php

namespace Vanguard\Singletones;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\Folder;
use Vanguard\Modules\MenuTreeModule;
use Vanguard\User;

class OtherUserModule extends AuthUserModule
{
    /**
     * AuthUserModule constructor.
     * @param int|null $user_id
     */
    public function __construct(int $user_id = null)
    {
        $this->user = $user_id
            ? User::find($user_id)
            : new User();

        $this->menuTreeModule = new MenuTreeModule($this);
    }
}