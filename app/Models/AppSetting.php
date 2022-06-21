<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\AppSetting
 *
 * @property int $id
 * @property string $key
 * @property string $val
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\AppSetting whereVal($value)
 * @mixin \Eloquent
 */
class AppSetting extends Model
{
    protected $table = 'app_settings';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'val',
    ];
}
