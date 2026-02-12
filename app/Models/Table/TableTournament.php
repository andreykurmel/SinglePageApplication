<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRowGroup;

/**
 * Vanguard\Models\Table\TableTournament
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string|null $description
 * @property int $tour_active
 * @property int|null $tb_tour_data_range
 * @property int|null $teamhomename_fld_id
 * @property int|null $teamhomegoals_fld_id
 * @property int|null $teamguestname_fld_id
 * @property int|null $teamguestgoals_fld_id
 * @property int|null $stage_fld_id
 * @property int|null $date_fld_id
 * @property int $p_team_width
 * @property int $p_goal_width
 * @property int $p_match_margin
 * @property int $p_round_margin
 * @mixin Eloquent
 */
class TableTournament extends Model
{
    public $timestamps = false;

    protected $table = 'table_tournaments';

    protected $fillable = [
        'table_id',
        'name',
        'description',
        'tour_active',
        'tb_tour_data_range',
        'teamhomename_fld_id',
        'teamhomegoals_fld_id',
        'teamguestname_fld_id',
        'teamguestgoals_fld_id',
        'stage_fld_id',
        'date_fld_id',
        'date_fld_id',
        'p_team_width',
        'p_goal_width',
        'p_match_margin',
        'p_round_margin',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_tournament_rights', 'table_tournament_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_tournament_id', 'table_permission_id', 'can_edit']);
    }

    public function _tournament_rights()
    {
        return $this->hasMany(TableTournamentRight::class, 'table_tournament_id', 'id');
    }
}
