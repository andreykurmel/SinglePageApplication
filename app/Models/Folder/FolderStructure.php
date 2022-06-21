<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\FolderStructure
 *
 * @property int $id
 * @property int $element_id
 * @property int $child_id
 * @property int $parent_id
 * @property int|null $user_id
 * @property-read \Vanguard\Models\Folder\Folder $_folder
 * @property-read \Vanguard\Models\Folder\Folder $_parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\Folder2Table[] $_table_links
 * @property-read int|null $_table_links_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\Table[] $_tables
 * @property-read int|null $_tables_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure joinParentFolders()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure whereChildId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure whereElementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderStructure whereUserId($value)
 * @mixin \Eloquent
 */
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
            ->withPivot(['id', 'user_id', 'type', 'structure', 'is_folder_link']);
    }
    // <-- clone Folder
}
