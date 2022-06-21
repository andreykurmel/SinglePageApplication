<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TablePermissionDefaultField
 *
 * @property int $id
 * @property int $table_permission_id
 * @property int $table_field_id
 * @property string $default
 * @property int|null $user_group_id
 * @property-read \Vanguard\User $_created_user
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @property-read \Vanguard\Models\User\UserGroup|null $_user_group
 * @property-read \Vanguard\Models\User\UserGroup2TablePermission|null $_user_group_2_permissions
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField hasUserGroupForUser($user_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField whereTableFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField whereTablePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField whereUserGroupId($value)
 * @mixin \Eloquent
 */
class TablePermissionDefaultField extends Model
{
    protected $table = 'table_permission_def_fields';

    public $timestamps = false;

    protected $fillable = [
        'table_permission_id',
        'user_group_id',
        'table_field_id',
        'default',
    ];


    public function scopeHasUserGroupForUser($builder, $user_id) {
        return $builder->where(function ($wh) use ($user_id) {
            //user is member of userGroup
            $wh->whereHas('_user_group_2_permissions', function ($ug) use ($user_id) {
                $ug->where('is_active', 1);
            });
        });
    }


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _user_group() {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }

    public function _user_group_2_permissions() {
        return $this->belongsTo(UserGroup2TablePermission::class, 'user_group_id', 'user_group_id')
            ->whereRaw('table_permission_def_fields.table_permission_id = user_groups_2_table_permissions.table_permission_id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
