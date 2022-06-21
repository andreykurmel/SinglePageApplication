<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableRefConditionItem
 *
 * @property int $id
 * @property int $table_ref_condition_id
 * @property string|null $logic_operator
 * @property int|null $table_field_id
 * @property string|null $compared_operator
 * @property int|null $compared_field_id
 * @property string|null $compared_value
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string $item_type
 * @property string $group_clause
 * @property string|null $group_logic
 * @property string|null $spec_show
 * @property-read \Vanguard\Models\Table\TableField|null $_compared_field
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\Models\Table\TableField|null $_field
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition $_ref_condition
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereComparedFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereComparedOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereComparedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereGroupClause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereGroupLogic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereLogicOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereSpecShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereTableFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefConditionItem whereTableRefConditionId($value)
 * @mixin \Eloquent
 */
class TableRefConditionItem extends Model
{
    protected $table = 'table_ref_condition_items';

    public $timestamps = false;

    protected $fillable = [
        'table_ref_condition_id',
        'logic_operator',
        'table_field_id',
        'compared_operator',
        'compared_field_id',
        'compared_value',
        'spec_show',
        'item_type',
        'group_clause',
        'group_logic',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _ref_condition() {
        return $this->belongsTo(TableRefCondition::class, 'table_ref_condition_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }

    public function _compared_field() {
        return $this->hasOne(TableField::class, 'id', 'compared_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
