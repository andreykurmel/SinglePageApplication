<?php

namespace Vanguard\Models\User;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\User\UserSubscription
 *
 * @property int $id
 * @property int $addon_id
 * @property int $user_subscription_id
 * @mixin Eloquent
 */
class UserSubscription2Addons extends Model
{
    public $timestamps = false;

    protected $table = 'user_subscriptions_2_addons';

    protected $fillable = [
        'addon_id',
        'user_subscription_id',
    ];
}
