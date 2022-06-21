<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

/**
 * Vanguard\Models\User\Addon
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property float $per_month
 * @property float $per_year
 * @property string|null $notes
 * @property int|null $is_special
 * @property int $rowpos
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_permissions
 * @property-read int|null $_permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon isAlert()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon isBi()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereIsSpecial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon wherePerMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon wherePerYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Addon whereRowpos($value)
 * @mixin \Eloquent
 */
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
    public function scopeIsAlert($builder) {
        $builder->where('code', '=', 'alert');
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
