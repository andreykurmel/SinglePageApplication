<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\User;

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
            //or def field is for DataRequest for all users
            $wh->orWhereNull('user_group_id');
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
