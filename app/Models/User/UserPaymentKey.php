<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserPaymentKey extends Model
{
    protected $table = 'user_payment_keys';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type', // 'stripe','paypal'
        'mode', // 'live','sandbox'
        'name',
        'secret_key',
        'public_key',
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
