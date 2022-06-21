<?php

namespace Vanguard\Services\Auth\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Services\Auth\Api\Token
 *
 * @property string $id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $expires_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Auth\Api\Token whereUserId($value)
 * @mixin \Eloquent
 */
class Token extends Model
{
    protected $table = 'api_tokens';

    public $incrementing = false;
}
