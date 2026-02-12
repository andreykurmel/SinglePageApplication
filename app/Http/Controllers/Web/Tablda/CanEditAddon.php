<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableReport;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Singletones\AuthUserSingleton;

trait CanEditAddon
{
    /**
     * @param Table $table
     * @param HasMany $rightsRelation
     * @return bool
     */
    protected function canViewAddonItem(Table $table, HasMany $rightsRelation): bool
    {
        if (auth()->user()->can('isOwner', [TableData::class, $table])) {
            return true;
        }
        $auth = app(AuthUserSingleton::class);
        return !!$rightsRelation
            ->whereIn('table_permission_id', $auth->getTablePermissionIdsMember())
            ->count();
    }
    /**
     * @param Table $table
     * @param HasMany $rightsRelation
     * @return bool
     */
    protected function canEditAddonItem(Table $table, HasMany $rightsRelation): bool
    {
        if (auth()->user()->can('isOwner', [TableData::class, $table])) {
            return true;
        }
        $auth = app(AuthUserSingleton::class);
        return !!$rightsRelation
            ->whereIn('table_permission_id', $auth->getTablePermissionIdsMember())
            ->where('can_edit', '=', 1)
            ->count();
    }

    /**
     * @param Table $table
     * @param string $neededCode
     * @return bool
     */
    protected function canEditAddon(Table $table, string $neededCode): bool
    {
        if (auth()->user()->can('isOwner', [TableData::class, $table])) {
            return true;
        }
        $permis = TableRights::permissions($table);
        return !!$permis->_addons->filter(function ($adn) use ($neededCode) {
            return $adn['code'] == $neededCode && $adn['_link']['type'] == 'edit';
        })->count();
    }
}