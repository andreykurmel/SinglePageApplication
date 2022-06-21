<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\SubscribedApp
 *
 * @property int $id
 * @property int $user_id
 * @property int $app_id
 * @property-read \Vanguard\User|null $_available_features
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\SubscribedApp whereUserId($value)
 * @mixin \Eloquent
 */
class SubscribedApp extends Model
{
    protected $table = 'user_subscribed_apps';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'app_id',
    ];


    public function _available_features() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
