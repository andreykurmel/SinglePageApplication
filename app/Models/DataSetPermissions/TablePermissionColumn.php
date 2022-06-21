<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TablePermissionColumn
 *
 * @property int $id
 * @property int $table_permission_id
 * @property int $table_column_group_id
 * @property int $view
 * @property int $edit
 * @property int $shared
 * @property int $delete
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup $_column_group
 * @property-read \Vanguard\User $_created_user
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereTableColumnGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereTablePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionColumn whereView($value)
 * @mixin \Eloquent
 */
class TablePermissionColumn extends Model
{
    protected $table = 'table_permissions_2_table_column_groups';

    public $timestamps = false;

    protected $fillable = [
        'table_permission_id',
        'table_column_group_id',
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

    public function _column_group() {
        return $this->belongsTo(TableColumnGroup::class, 'table_column_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
