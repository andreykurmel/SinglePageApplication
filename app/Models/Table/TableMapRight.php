<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_map_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableMap $_map
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableMapRight extends Model
{
    protected $table = 'table_map_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_map_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _map()
    {
        return $this->belongsTo(TableMap::class, 'table_map_id', 'id');
    }
}
