<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

class TableData extends Model
{
    protected $connection = 'mysql_data';

    protected $guarded = [];

    protected $table = '';

    public $timestamps = false;
}
