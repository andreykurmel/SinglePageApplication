<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

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
