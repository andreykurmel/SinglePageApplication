<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class AppTheme extends Model
{
    const USERS_THEMES_COUNT = 3;

    protected $table = 'app_themes';

    public $timestamps = false;

    protected $fillable = [
        'obj_type', // ['system','user','table']
        'obj_id',
        'navbar_bg_color',
        'ribbon_bg_color',
        'button_bg_color',
        'main_bg_color',
        'table_hdr_bg_color',
        'app_font_size',
        'app_font_color',
    ];
}
