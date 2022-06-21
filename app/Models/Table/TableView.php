<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableView
 *
 * @property int $id
 * @property int $table_id
 * @property int|null $user_id
 * @property string $hash
 * @property string|null $user_link
 * @property string $name
 * @property string $data
 * @property int|null $access_permission_id
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int $is_active
 * @property int $is_locked
 * @property string $lock_pass
 * @property int|null $row_group_id
 * @property int|null $col_group_id
 * @property int $can_sorting
 * @property int $can_show_srv
 * @property string $side_top
 * @property string $side_left_menu
 * @property string $side_left_filter
 * @property string $side_right
 * @property int $column_order
 * @property string|null $parts_avail
 * @property string|null $parts_default
 * @property int $is_system
 * @property int|null $view_filtering
 * @property int $can_filter
 * @property int $can_hide
 * @property \Vanguard\Models\DataSetPermissions\TablePermission|null $_access_permission
 * @property \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_col_group
 * @property \Vanguard\User|null $_created_user
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableViewFiltering[] $_filtering
 * @property int|null $_filtering_count
 * @property \Vanguard\User|null $_modified_user
 * @property \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_row_group
 * @property \Vanguard\Models\Table\Table $_table
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @property \Vanguard\User|null $_user
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableViewRight[] $_view_rights
 * @property int|null $_view_rights_count
 * @mixin \Eloquent
 */
class TableView extends Model
{
    protected $table = 'table_views';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'is_system',
        'hash',
        'data',

        'name',
        'parts_avail',
        'parts_default',
        'row_group_id',
        'col_group_id',
        'access_permission_id',
        'view_filtering',

        'side_top',
        'side_left_menu',
        'side_left_filter',
        'side_right',
        'can_show_srv',
        'can_sorting',
        'can_filter',
        'can_hide',
        'column_order',
        'is_active',
        'is_locked',
        'lock_pass',
        'user_link',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _view_rights() {
        return $this->hasMany(TableViewRight::class, 'table_view_id', 'id');
    }

    public function _filtering() {
        return $this->hasMany(TableViewFiltering::class, 'table_view_id', 'id')
            ->where('active', '=', 1);
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_view_rights', 'table_view_id', 'table_permission_id');
    }

    public function _access_permission() {
        return $this->hasOne(TablePermission::class, 'id', 'access_permission_id');
    }

    public function _row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'row_group_id');
    }
    public function _col_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'col_group_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
