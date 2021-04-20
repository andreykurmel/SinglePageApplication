<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class CountryData extends Model
{
    protected $table = 'countries';

    public $timestamps = false;
}
