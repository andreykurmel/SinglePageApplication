<?php

namespace Vanguard\Services\Logging\UserActivity;

use Vanguard\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Services\Logging\UserActivity\Activity
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
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Vanguard\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereDescriptionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereDifferenceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereEnding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereEndingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Services\Logging\UserActivity\Activity whereYear($value)
 * @mixin \Eloquent
 */
class Activity extends Model
{
    const UPDATED_AT = null;

    protected $table = 'user_activity';

    protected $fillable = [
        'user_id',
        'description',
        'description_time',
        'ending',
        'ending_time',
        'difference_time',
        'lat',
        'lng',
        'year',
        'month',
        'week',
        'ip_address',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
