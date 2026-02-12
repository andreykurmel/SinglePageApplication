<?php

namespace Vanguard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\AutomationHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $table_id
 * @property string $function
 * @property string $name
 * @property string $component
 * @property string $part
 * @property string $exec_time
 * @property Carbon $start_time
 * @property string $year
 * @property string $month
 * @property string $week
 * @property string $day
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property-read \Vanguard\User $_user
 * @mixin \Eloquent
 */
class AutomationHistory extends Model
{
    protected $table = 'automation_histories';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'table_id',
        'function',
        'name',
        'component',
        'part',
        'exec_time',
        'start_time',
        'year',
        'month',
        'week',
        'day',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
