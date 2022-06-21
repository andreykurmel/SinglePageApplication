<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TablePermissionRow
 *
 * @property int $id
 * @property int $table_permission_id
 * @property int $table_row_group_id
 * @property int $view
 * @property int $edit
 * @property int $shared
 * @property int $delete
 * @property-read \Vanguard\User $_created_user
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup $_row_group
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereTablePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereTableRowGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionRow whereView($value)
 * @mixin \Eloquent
 */
class TablePermissionRow extends Model
{
    protected $table = 'table_permissions_2_table_row_groups';

    public $timestamps = false;

    protected $fillable = [
        'table_permission_id',
        'table_row_group_id',
        'view',
        'edit',
        'delete',
        'shared',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _row_group() {
        return $this->belongsTo(TableRowGroup::class, 'table_row_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
