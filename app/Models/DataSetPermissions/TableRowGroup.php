<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class TableRowGroup extends Model
{
    protected $table = 'table_row_groups';

    public $timestamps = false;

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


    public function scopeActiveForUser($q, int $table_permission_id = null) {
        return $q->whereHas('_table_permission_rows', function ($tpr) use ($table_permission_id) {
            $tpr->where('shared', 1);
            $tpr->whereHas('_table_permission', function ($tp) use ($table_permission_id) {
                $tp->applyIsActiveForUserOrPermission($table_permission_id, true);
            });
        });
    }


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _conditions() {
        return $this->hasMany(TableRowGroupCondition::class, 'table_row_group_id', 'id');
    }

    // REF CONDITIONS
    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'row_ref_condition_id');
    }

    public function _is_direct_ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'row_ref_condition_id')
            ->whereHas('_items', function ($items) {
                $items->where('compared_value', '{$user}');
            });
    }

    public function _is_group_ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'row_ref_condition_id')
            ->whereHas('_items', function ($items) {
                $items->where('compared_value', '{$group}');
            });
    }
    //^^^^^

    public function _regulars() {
        return $this->hasMany(TableRowGroupRegular::class, 'table_row_group_id', 'id');
    }

    public function _table_permission_rows() {
        return $this->hasMany(TablePermissionRow::class, 'table_row_group_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_permissions_2_table_row_groups', 'table_row_group_id', 'table_permission_id')
            ->as('_link')
            ->withPivot(['view', 'edit', 'shared']);
    }

    public function _cond_formats() {
        return $this->hasMany(CondFormat::class, 'table_row_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
