<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserCard
 *
 * @property int $id
 * @property int $user_id
 * @property string $stripe_card_id
 * @property string $stripe_card_last
 * @property int|null $stripe_exp_month
 * @property int|null $stripe_exp_year
 * @property string|null $stripe_card_name
 * @property string|null $stripe_card_zip
 * @property string $stripe_card_brand
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeCardLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeCardZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeExpMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereStripeExpYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCard whereUserId($value)
 * @mixin \Eloquent
 */
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
