<?php

namespace Vanguard\Models\DataSetPermissions;

use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DDLReference;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableRefCondition
 *
 * @property int $id
 * @property int $table_id
 * @property int $ref_table_id
 * @property int|null $base_refcond_id
 * @property int $incoming_allow
 * @property int $is_system
 * @property int $rc_static
 * @property string $name
 * @property string|null $notes
 * @property int $row_order
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property-read User|null $_created_user
 * @property-read Collection|TableRefConditionItem[] $_items
 * @property-read int|null $_items_count
 * @property-read User|null $_modified_user
 * @property-read Table|null $_ref_table
 * @property-read TableRefCondition|null $base_refcond
 * @property-read Table $_table
 * @mixin Eloquent
 */
class TableRefCondition extends Model
{
    protected $table = 'table_ref_conditions';

    public $timestamps = false;

    protected $fillable = [
        'incoming_allow',
        'table_id',
        'ref_table_id',
        'base_refcond_id',
        'is_system',
        'rc_static',
        'name',
        'notes',
        'row_order',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _ref_table()
    {
        return $this->hasOne(Table::class, 'id', 'ref_table_id');
    }

    public function _base_refcond()
    {
        return $this->hasOne(TableRefCondition::class, 'id', 'base_refcond_id');
    }

    public function _items()
    {
        return $this->hasMany(TableRefConditionItem::class, 'table_ref_condition_id', 'id');
    }

    public function _applied_ddlrefs()
    {
        return $this->hasMany(DDLReference::class, 'table_ref_condition_id', 'id');
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
