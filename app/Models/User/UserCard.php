<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserCard extends Model
{
    protected $table = 'user_cards';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'stripe_card_id',
        'stripe_card_last',
        'stripe_exp_month',
        'stripe_exp_year',
        'stripe_card_name',
        'stripe_card_zip',
        'stripe_card_brand',
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
