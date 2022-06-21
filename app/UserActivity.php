<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\UserActivity
 *
 * @property int $id
 * @property string $description
 * @property int|null $description_time
 * @property string|null $ending
 * @property int|null $ending_time
 * @property int|null $difference_time
 * @property float|null $lat
 * @property float|null $lng
 * @property int|null $year
 * @property int|null $month
 * @property int|null $week
 * @property int $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereDescriptionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereDifferenceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereEnding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereEndingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\UserActivity whereYear($value)
 * @mixin \Eloquent
 */
class UserActivity extends Model
{
    public $timestamps = false;

    protected $table = 'user_activity';

    protected $fillable = ['description', 'user_id', 'ip_address', 'user_agent'];
}
