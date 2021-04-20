<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class FolderStructure extends Model
{
    protected $table = 'folder_trees';

    public $timestamps = false;

    protected $fillable = [
        'element_id',
        'child_id',
        'parent_id',
        'user_id',
    ];


    public function scopeJoinParentFolders($builder) {
        return $builder->join('folder_trees as f2', function ($q) {
                $q->whereRaw('folder_trees.element_id = f2.child_id');
            })
            ->whereRaw('f2.element_id = f2.child_id');
    }

    public function _folder() {
        return $this->belongsTo(Folder::class, 'child_id', 'id');
    }

    public function _parent() {
        return $this->belongsTo(Folder::class, 'parent_id', 'id');
    }

    // clone Folder -->
    public function _table_links() {
        return $this->hasMany(Folder2Table::class, 'folder_id', 'element_id');
    }

    public function _tables() {
        return $this
            ->belongsToMany(Table::class, 'folders_2_tables', 'folder_id', 'table_id')
            ->as('link')
            ->withPivot(['id', 'user_id', 'type', 'structure']);
    }
    // <-- clone Folder
}
