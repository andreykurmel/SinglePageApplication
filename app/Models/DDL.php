<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

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


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
