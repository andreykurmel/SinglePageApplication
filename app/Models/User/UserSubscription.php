<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class UserSubscription extends Model
{
    protected $table = 'user_subscriptions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'active',
        'plan_code',
        'left_days',
        'total_days',
        'cost'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _addons() {
        return $this->belongsToMany(Addon::class, 'user_subscriptions_2_addons', 'user_subscription_id', 'addon_id');
    }

    public function _plan() {
        return $this->hasOne(Plan::class, 'code', 'plan_code');
    }
}
