<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableRefCondition
 *
 * @property int $id
 * @property int $table_id
 * @property int $ref_table_id
 * @property int $incoming_allow
 * @property int $is_system
 * @property string $name
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableRefConditionItem[] $_items
 * @property-read int|null $_items_count
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table|null $_ref_table
 * @property-read \Vanguard\Models\Table\Table $_table
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereIncomingAllow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereRefTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRefCondition whereTableId($value)
 * @mixin \Eloquent
 */
class TableRefCondition extends Model
{
    protected $table = 'table_ref_conditions';

    public $timestamps = false;

    protected $fillable = [
        'incoming_allow',
        'table_id',
        'ref_table_id',
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

    public function _ref_table() {
        return $this->hasOne(Table::class, 'id', 'ref_table_id');
    }

    public function _items() {
        return $this->hasMany(TableRefConditionItem::class, 'table_ref_condition_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
