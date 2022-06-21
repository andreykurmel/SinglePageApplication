<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserPaymentKey
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $mode
 * @property string $name
 * @property string|null $secret_key
 * @property string|null $public_key
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereSecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserPaymentKey whereUserId($value)
 * @mixin \Eloquent
 */
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
