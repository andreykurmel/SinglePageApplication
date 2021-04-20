<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserInvitation extends Model
{
    protected $table = 'user_invitations';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'date_send',
        'date_accept',
        'status'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
