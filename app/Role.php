<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Support\Authorization\AuthorizationRoleTrait;

/**
 * Vanguard\Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property bool $removable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereRemovable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    use AuthorizationRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    protected $casts = [
        'removable' => 'boolean'
    ];

    protected $fillable = ['name', 'display_name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
