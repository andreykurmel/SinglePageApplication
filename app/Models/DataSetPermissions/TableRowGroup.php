<?php

namespace Vanguard\Models\DataSetPermissions;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableRowGroup
 *
 * @property int $id
 * @property int $table_id
 * @property int $is_system
 * @property string $name
 * @property string|null $description
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $listing_field
 * @property int $is_showed
 * @property int|null $row_ref_condition_id
 * @property int|null $preview_col_id
 * @property CondFormat[] $_cond_formats
 * @property int|null $_cond_formats_count
 * @property int|null $_conditions_count
 * @property User|null $_created_user
 * @property User|null $_modified_user
 * @property TableRefCondition|null $_ref_condition
 * @property TableColumnGroup|null $_preview_col
 * @property TableRowGroupRegular[] $_regulars
 * @property int|null $_regulars_count
 * @property Table $_table
 * @property TablePermissionRow[] $_table_permission_rows
 * @property int|null $_table_permission_rows_count
 * @property TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @mixin Eloquent
 */
class TableRowGroup extends Model
{
    public $timestamps = false;

    protected $table = 'table_row_groups';

    protected $fillable = [
        'is_system',
        'table_id',
        'name',
        'listing_field',
        'is_showed',
        'row_ref_condition_id',
        'preview_col_id',
        'description',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function scopeActiveForUser($q, int $table_permission_id = null)
    {
        return $q->whereHas('_table_permission_rows', function ($tpr) use ($table_permission_id) {
            $tpr->where('shared', 1);
            $tpr->whereHas('_table_permission', function ($tp) use ($table_permission_id) {
                $tp->applyIsActiveForUserOrPermission($table_permission_id, true);
            });
        });
    }


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _ref_condition()
    {
        return $this->hasOne(TableRefCondition::class, 'id', 'row_ref_condition_id');
    }

    public function _preview_col()
    {
        return $this->hasOne(TableColumnGroup::class, 'id', 'preview_col_id');
    }

    public function _regulars()
    {
        return $this->hasMany(TableRowGroupRegular::class, 'table_row_group_id', 'id');
    }

    public function _table_permission_rows()
    {
        return $this->hasMany(TablePermissionRow::class, 'table_row_group_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_permissions_2_table_row_groups', 'table_row_group_id', 'table_permission_id')
            ->as('_link')
            ->withPivot(['view', 'edit', 'delete', 'shared']);
    }

    public function _cond_formats()
    {
        return $this->hasMany(CondFormat::class, 'table_row_group_id', 'id');
    }


    public function _created_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
