<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableTournamentRight
 *
 * @property int $id
 * @property int $table_tournament_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableTwilioAddonSetting $_tournament
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableTournamentRight extends Model
{
    protected $table = 'table_tournament_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_tournament_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _tournament()
    {
        return $this->belongsTo(TableTournament::class, 'table_tournament_id', 'id');
    }
}
