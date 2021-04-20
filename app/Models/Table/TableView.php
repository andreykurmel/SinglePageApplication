<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

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
        'can_sorting',
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
