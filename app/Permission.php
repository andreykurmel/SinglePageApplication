<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Permission
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property bool $removable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereRemovable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'display_name', 'description'];

    protected $casts = [
        'removable' => 'boolean'
    ];
}
