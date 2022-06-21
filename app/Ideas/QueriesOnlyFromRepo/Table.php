<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Ideas\RepositoryFactory;
use Vanguard\User;

/**
 * Vanguard\Ideas\QueriesOnlyFromRepo\Table
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\Table query()
 * @mixin \Eloquent
 */
class Table extends Model
{
    protected $table = '';

    /**
     * Access to DB should be used just from Repository
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function db() {
        return (new Table())->setTable('tables')->newQuery();
    }


    //settings
    public $timestamps = false;

    protected $fillable = [
        'id',
        'db_name',
        'name',
        'user_id',
    ];

    /**
     *  example #model_hook
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
     * @return \Illuminate\Support\Collection
     */
    public function _fields() {
        return (RepositoryFactory::TableField())->getByTable( $this->id );
    }
}
