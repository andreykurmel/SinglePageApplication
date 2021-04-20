<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserEmailAccount extends Model
{
    protected $table = 'user_email_accounts';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'app_pass',
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
