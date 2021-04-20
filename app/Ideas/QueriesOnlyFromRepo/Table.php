<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class Table extends Model
{
    protected $table = 'tables';

    public $timestamps = false;

    //should be filled all columns
    protected $fillable = [
        'id',
        'db_name',
        'name',
        'user_id',
        'rows_per_page',
        'notes',
        'add_notes',
    ];

    protected $not_copy = [
        'db_name',
        'name',
        'user_id',
    ];

    /**
     *  #model_hook
     */
    public static function boot()
    {
        parent::boot();
        Table::creating(function ($data) { Table::on_creating($data); });
    }

    public static function on_creating($data) {
        $data['is_system'] = $data['is_system'] ?? 0;
    }
    //-----------------------

    /**
     * @return array
     */
    public function getCopyAttrs() {
        return collect( $this->getAttributes() )
            ->except( $this->not_copy )
            ->toArray();
    }



    public function _table() {
        return $this->hasOne(Table::class, 'id', 'id');
    }

    public function _fields() {
        return $this->hasMany(Table::class, 'id', 'id');
    }
}
