<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\ResetPass
 *
 * @property int $id
 * @property string $email
 * @property string $token
 * @property string $web_token
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\ResetPass whereWebToken($value)
 * @mixin \Eloquent
 */
class ResetPass extends Model
{
    protected $table = 'password_resets';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'web_token',
    ];
}
