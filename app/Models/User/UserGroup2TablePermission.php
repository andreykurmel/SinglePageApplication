<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\User\UserGroup2TablePermission
 *
 * @property int $id
 * @property int $user_group_id
 * @property int|null $table_permission_id
 * @property int $is_active
 * @property int|null $table_id
 * @property int $is_app
 * @property int $is_hidden
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereIsApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereTablePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroup2TablePermission whereUserGroupId($value)
 * @mixin \Eloquent
 */
class UserGroup2TablePermission extends Model
{
    protected $table = 'user_groups_2_table_permissions';

    public $timestamps = false;

    protected $fillable = [
        'user_group_id',
        'table_permission_id',
        'table_id',
        'is_app',
        'is_active',
        'is_hidden',
    ];
}
