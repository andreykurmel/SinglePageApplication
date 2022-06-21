<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DDLReference
 *
 * @property int $id
 * @property int $ddl_id
 * @property int $table_ref_condition_id
 * @property int|null $target_field_id
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $image_field_id
 * @property int|null $apply_target_row_group_id
 * @property int|null $show_field_id
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_apply_row_group
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\DDL $_ddl
 * @property-read \Vanguard\Models\Table\TableField|null $_image_field
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_condition
 * @property-read \Vanguard\Models\Table\TableField|null $_show_field
 * @property-read \Vanguard\Models\Table\TableField|null $_target_field
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference isTbRef()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereApplyTargetRowGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereDdlId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereImageFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereShowFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereSortType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereTableRefConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReference whereTargetFieldId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DDLReferenceColor[] $_reference_colors
 * @property-read int|null $_reference_colors_count
 */
class DDLReference extends Model
{
    protected $table = 'ddl_references';

    public $timestamps = false;

    protected $fillable = [
        'ddl_id',
        'table_ref_condition_id',
        'apply_target_row_group_id',
        'target_field_id',
        'image_field_id',
        'show_field_id',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    /**
     * @param $q
     * @return mixed
     */
    public function scopeIsTbRef($q) {
        return $q->where(function ($i) {
            //$i->whereNotNull('target_field_id');
            $i->whereNotNull('show_field_id');
            $i->orWhereNotNull('image_field_id');
        });
    }


    /**
     * @return Table\Table|null
     */
    public function ref_table()
    {
        return $this->_ref_condition && $this->_ref_condition->incoming_allow ? $this->_ref_condition->_ref_table : null;
    }


    //RELATIONS
    public function _ddl() {
        return $this->belongsTo(DDL::class, 'ddl_id', 'id');
    }

    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _apply_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'apply_target_row_group_id');
    }

    public function _target_field() {
        return $this->hasOne(TableField::class, 'id', 'target_field_id');
    }

    public function _image_field() {
        return $this->hasOne(TableField::class, 'id', 'image_field_id');
    }

    public function _show_field() {
        return $this->hasOne(TableField::class, 'id', 'show_field_id');
    }

    public function _reference_colors() {
        return $this->hasMany(DDLReferenceColor::class, 'ddl_reference_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
