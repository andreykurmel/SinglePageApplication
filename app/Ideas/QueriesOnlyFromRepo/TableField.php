<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Ideas\RepositoryFactory;
use Vanguard\User;

/**
 * Vanguard\Ideas\QueriesOnlyFromRepo\TableField
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\TableField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\TableField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Ideas\QueriesOnlyFromRepo\TableField query()
 * @mixin \Eloquent
 */
class TableField extends Model
{
    protected $table = '';

    /**
     * Access to DB should be used just from Repository
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function db() {
        return (new TableField())->setTable('table_fields')->newQuery();
    }


    //settings
    public $timestamps = false;
    protected $visible = ['id','table_id'];

    protected $fillable = [
        'id',
        'table_id',
        'name',
        'field',
    ];
    //-----------------------


    /**
     * @return Table
     */
    public function _table() {
        return (RepositoryFactory::Table())->first( $this->table_id );
    }
}
