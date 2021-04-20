<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableViewRight;
use Vanguard\User;

class UserGroup extends Model
{
    protected $table = 'user_groups';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'notes',
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //Morph relations
    public function _tables() {
        return $this->morphedByMany(
            Table::class,
            'object',
            'user_groups_3_tables_and_folders'
        );
    }

    public function _folders() {
        return $this->morphedByMany(
            Folder::class,
            'object',
            'user_groups_3_tables_and_folders'
        );
    }
    //----------

    public function _table_permissions() {
        return $this
            ->belongsToMany(TablePermission::class, 'user_groups_2_table_permissions', 'user_group_id', 'table_permission_id')
            ->withPivot(['id', 'table_permission_id', 'user_group_id', 'is_active', 'is_app'])
            ->wherePivot('is_active', '=', 1);
    }

    public function _tables_shared() {
        return $this->hasMany(UserGroup2TablePermission::class, 'user_group_id', 'id');
    }

    public function _individuals() {
        return $this->belongsToMany(User::class, 'user_groups_2_users', 'user_group_id', 'user_id')
            ->as('_link')
            ->withPivot(['user_group_id', 'user_id', 'cached_from_conditions', 'is_edit_added'])
            ->wherePivot('cached_from_conditions', '=', 0);
    }

    public function _individuals_all() {
        return $this->belongsToMany(User::class, 'user_groups_2_users', 'user_group_id', 'user_id')
            ->as('_link')
            ->withPivot(['user_group_id', 'user_id', 'cached_from_conditions', 'is_edit_added']);
    }

    public function _links() {
        return $this->hasMany(UserGroupLink::class, 'user_group_id', 'id');
    }

    public function _conditions() {
        return $this->hasMany(UserGroupCondition::class, 'user_group_id', 'id');
    }
}
