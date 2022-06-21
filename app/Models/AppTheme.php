<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\AppTheme
 *
 * @property int $id
 * @property string $obj_type
 * @property int|null $obj_id
 * @property string|null $navbar_bg_color
 * @property string|null $ribbon_bg_color
 * @property string|null $button_bg_color
 * @property string|null $main_bg_color
 * @property string|null $table_hdr_bg_color
 * @property int|null $app_font_size
 * @property string|null $app_font_color
 * @property string|null $app_font_family
 * @property int|null $appsys_font_size
 * @property string|null $appsys_font_color
 * @property string|null $appsys_font_family
 * @property int|null $appsys_tables_font_size
 * @property string|null $appsys_tables_font_color
 * @property string|null $appsys_tables_font_family
 * @mixin \Eloquent
 */
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
        'app_font_family',
        'appsys_font_size',
        'appsys_font_color',
        'appsys_font_family',
        'appsys_tables_font_size',
        'appsys_tables_font_color',
        'appsys_tables_font_family',
    ];
}
