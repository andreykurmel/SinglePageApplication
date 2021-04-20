<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'val',
    ];
}
