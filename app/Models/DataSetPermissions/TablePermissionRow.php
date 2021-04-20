<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

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
