<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserApiKey extends Model
{
    protected $table = 'user_api_keys';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type', // 'google','sendgrid'
        'key',
        'is_active',
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
