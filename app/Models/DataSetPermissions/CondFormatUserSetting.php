<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\CondFormatUserSetting
 *
 * @property int $id
 * @property int $cond_format_id
 * @property int $user_id
 * @property int $status
 * @property-read \Vanguard\Models\DataSetPermissions\CondFormat $_cond_format
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting whereCondFormatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\CondFormatUserSetting whereUserId($value)
 * @mixin \Eloquent
 */
class CondFormatUserSetting extends Model
{
    protected $table = 'cond_format_user_settings';

    public $timestamps = false;

    protected $fillable = [
        'cond_format_id',
        'user_id',
        'status',
    ];


    public function _cond_format() {
        return $this->belongsTo(CondFormat::class, 'table_id', 'cond_format_id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
