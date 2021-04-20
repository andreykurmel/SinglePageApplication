<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableColumnGroup extends Model
{
    protected $table = 'table_column_groups';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'is_system',
        'name',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _fields() {
        return $this->belongsToMany(TableField::class, 'table_column_groups_2_table_fields', 'table_column_group_id', 'table_field_id');
    }

    public function _table_permission_columns() {
        return $this->hasMany(TablePermissionColumn::class, 'table_column_group_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_permissions_2_table_column_groups', 'table_column_group_id', 'user_group_id')
            ->as('_link')
            ->withPivot(['view', 'edit', 'shared']);
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
