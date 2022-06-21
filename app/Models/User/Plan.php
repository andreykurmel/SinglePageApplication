<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\Plan
 *
 * @property int $id
 * @property string $name
 * @property float $per_month
 * @property float $per_year
 * @property string|null $notes
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int|null $plan_feature_id
 * @property string $code
 * @property-read \Vanguard\Models\User\PlanFeature|null $_available_features
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan wherePerMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan wherePerYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Plan wherePlanFeatureId($value)
 * @mixin \Eloquent
 */
class Plan extends Model
{
    protected $table = 'plans';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'plan_feature_id',
        'name',
        'per_month',
        'per_year',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _available_features() {
        return $this->hasOne(PlanFeature::class, 'id', 'plan_feature_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
