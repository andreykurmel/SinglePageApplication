<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

class Folder extends Model
{
    protected $table = 'folders';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'structure',
        'is_opened',
        'is_system',
        'in_shared',//0 - regular, 1 - in SHARED, 2 - in APPs
        'icon_path',
        'for_shared_user_id',
        'menutree_order',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function scopeJoinFolderStructure($builder) {
        return $builder->join('folder_trees', function ($q) {
                $q->whereRaw('folder_trees.element_id = folders.id');
                $q->whereRaw('folder_trees.child_id = folders.id');
            })
            ->selectRaw("folders.*, folder_trees.parent_id");
    }

    public function scopeJoinFolder2Table($builder) {
        return $builder->join('folders_2_tables', function ($q) {
            $q->whereRaw('folders_2_tables.folder_id = folders.id');
        });
    }


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _tables() {
        return $this
            ->belongsToMany(Table::class, 'folders_2_tables', 'folder_id', 'table_id')
            ->as('link')
            ->withPivot(['id', 'user_id', 'type', 'structure']);
    }

    public function _folder_views() {
        return $this->hasMany(FolderView::class, 'folder_id', 'id');
    }

    // Data Set Permissions
    public function _user_groups() {
        return $this->morphToMany(
            UserGroup::class,
            'object',
            'user_groups_3_tables_and_folders'
        );
    }

    //FOLDER TREE RELATIONS
    public function _root_folders() {
        return $this->hasMany(FolderStructure::class, 'child_id', 'id')
            ->join('folders', 'folders.id', '=', 'folder_trees.element_id')
            ->selectRaw('folder_trees.child_id, folder_trees.parent_id, folders.*');
    }

    public function _sub_folders() {
        return $this->hasMany(FolderStructure::class, 'element_id', 'id')
            ->join('folders', 'folders.id', '=', 'folder_trees.child_id')
            ->selectRaw('folder_trees.element_id, folder_trees.parent_id, folders.*');
    }

    public function _structure_record() {
        return $this->hasOne(FolderStructure::class, 'element_id', 'id')
            ->whereRaw('folder_trees.element_id = folder_trees.child_id');
    }
    //---------------------

    public function _table_links() {
        return $this->hasMany(Folder2Table::class, 'folder_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
