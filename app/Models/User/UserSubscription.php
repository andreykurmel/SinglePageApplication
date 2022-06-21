<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserSubscription
 *
 * @property int $id
 * @property int $active
 * @property int $user_id
 * @property string $plan_code
 * @property int $left_days
 * @property int $total_days
 * @property float $cost
 * @property string|null $notes
 * @property string|null $row_hash
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\Addon[] $_addons
 * @property-read int|null $_addons_count
 * @property-read \Vanguard\Models\User\Plan|null $_plan
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereLeftDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription wherePlanCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserSubscription whereUserId($value)
 * @mixin \Eloquent
 */
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
