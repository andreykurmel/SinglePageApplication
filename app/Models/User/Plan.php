<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

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
