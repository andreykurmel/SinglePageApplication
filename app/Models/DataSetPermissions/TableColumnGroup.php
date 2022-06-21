<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Dcr\TableDataRequestColumn;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableColumnGroup
 *
 * @property int $id
 * @property int $table_id
 * @property int $is_system
 * @property string $name
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property \Vanguard\User|null $_created_user
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableField[] $_fields
 * @property int|null $_fields_count
 * @property \Vanguard\User|null $_modified_user
 * @property \Vanguard\Models\Table\Table $_table
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Dcr\TableDataRequestColumn[] $_table_dcr_columns
 * @property int|null $_table_dcr_columns_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionColumn[] $_table_permission_columns
 * @property int|null $_table_permission_columns_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @mixin \Eloquent
 */
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

    public function _table_dcr_columns() {
        return $this->hasMany(TableDataRequestColumn::class, 'table_column_group_id', 'id');
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
