<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;

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
