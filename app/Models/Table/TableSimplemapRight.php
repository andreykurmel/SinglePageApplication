<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableSimplemapRight
 *
 * @property int $id
 * @property int $table_simplemap_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableTwilioAddonSetting $_simplemap
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableSimplemapRight extends Model
{
    protected $table = 'table_simplemap_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_simplemap_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _simplemap()
    {
        return $this->belongsTo(TableSimplemap::class, 'table_simplemap_id', 'id');
    }
}
