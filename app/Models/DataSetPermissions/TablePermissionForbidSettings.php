<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings
 *
 * @property int $id
 * @property int $permission_id
 * @property string $db_col_name
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings whereDbColName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings wherePermissionId($value)
 * @mixin \Eloquent
 */
class TablePermissionForbidSettings extends Model
{
    protected $table = 'table_permission_forbid_settings';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'db_col_name',
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }
}
