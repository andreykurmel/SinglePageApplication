<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableData
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableData query()
 * @mixin \Eloquent
 */
class TableData extends Model
{
    public $connection = 'mysql_data';

    protected $guarded = [];

    protected $table = '';

    public $timestamps = false;
}
