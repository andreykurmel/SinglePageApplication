<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DDL
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string $type
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string $items_pos
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableField[] $_applied_to_fields
 * @property-read int|null $_applied_to_fields_count
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DDLItem[] $_items
 * @property-read int|null $_items_count
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DDLReference[] $_references
 * @property-read int|null $_references_count
 * @property-read \Vanguard\Models\Table\Table $_table
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL hasIdReferences()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereItemsPos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereType($value)
 * @mixin \Eloquent
 * @property string|null $datas_sort
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDL whereDatasSort($value)
 */
class DDL extends Model
{
    protected $table = 'ddl';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'type',
        'notes',
        'items_pos',//['before','after']
        'datas_sort',//['asc','desc']
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    /**
     * @param $q
     * @return mixed
     */
    public function scopeHasIdReferences($q) {
        return $q->whereHas('_references', function ($i) {
                $i->isTbRef();
            })
            ->orWhereHas('_items', function ($i) {
                $i->isTbRef();
            });
    }


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _items() {
        return $this->hasMany(DDLItem::class, 'ddl_id', 'id');
    }

    public function _references() {
        return $this->hasMany(DDLReference::class, 'ddl_id', 'id');
    }

    public function _applied_to_fields() {
        return $this->hasMany(TableField::class, 'ddl_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
