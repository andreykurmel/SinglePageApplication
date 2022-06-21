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
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $listing_field
 * @property int $is_showed
 * @property int|null $row_ref_condition_id
 * @property CondFormat[] $_cond_formats
 * @property int|null $_cond_formats_count
 * @property int|null $_conditions_count
 * @property User|null $_created_user
 * @property User|null $_modified_user
 * @property TableRefCondition|null $_ref_condition
 * @property TableRowGroupRegular[] $_regulars
 * @property int|null $_regulars_count
 * @property Table $_table
 * @property TablePermissionRow[] $_table_permission_rows
 * @property int|null $_table_permission_rows_count
 * @property TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup activeForUser($table_permission_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereIsShowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereListingField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereRowRefConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroup whereTableId($value)
 */
class TableRowGroup extends Model
{
    public $timestamps = false;

    protected $table = 'table_row_groups';

    protected $fillable = [
        'table_id',
        'name',
        'listing_field',
        'is_showed',
        'row_ref_condition_id',
        'notes',
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
