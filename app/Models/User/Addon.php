<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

class Addon extends Model
{
    protected $table = 'addons';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'per_month',
        'per_year',
        'notes',
        'is_special',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function scopeIsBi($builder) {
        $builder->where('code', '=', 'bi');
    }


    public function _permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_permissions_2_addons', 'addon_id', 'table_permission_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
