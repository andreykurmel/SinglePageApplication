<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableTournamentRight
 *
 * @property int $id
 * @property int $table_grouping_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableGrouping $_grouping
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableGroupingRight extends Model
{
    protected $table = 'table_grouping_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_grouping_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _grouping()
    {
        return $this->belongsTo(TableGrouping::class, 'table_grouping_id', 'id');
    }
}
