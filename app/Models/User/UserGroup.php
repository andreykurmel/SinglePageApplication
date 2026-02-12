<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableViewRight;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserGroup
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $notes
 * @property int $is_system
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroupCondition[] $_conditions
 * @property int|null $_conditions_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\Folder[] $_folders
 * @property int|null $_folders_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\User[] $_individuals
 * @property int|null $_individuals_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\User[] $_individuals_all
 * @property int|null $_individuals_all_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroupLink[] $_links
 * @property int|null $_links_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\Table[] $_tables
 * @property int|null $_tables_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup2TablePermission[] $_tables_shared
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroupSubgroup[] $_subgroups
 * @property int|null $_tables_shared_count
 * @property \Vanguard\User $_user
 * @mixin \Eloquent
 */
class UserGroup extends Model
{
    protected $table = 'user_groups';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'notes',
        'is_system',
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

    public function _subgroups() {
        return $this->hasMany(UserGroupSubgroup::class, 'usergroup_id', 'id');
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
