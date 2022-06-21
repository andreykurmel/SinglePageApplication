<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\FolderView
 *
 * @property int $id
 * @property int $folder_id
 * @property string $hash
 * @property string|null $name
 * @property string|null $notes
 * @property int $is_active
 * @property string|null $user_link
 * @property int $is_locked
 * @property string|null $lock_pass
 * @property string $side_top
 * @property string $side_left_menu
 * @property string $side_right
 * @property int|null $def_table_id
 * @property string $side_left_filter
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\Table[] $_checked_tables
 * @property-read int|null $_checked_tables_count
 * @property-read \Vanguard\Models\Folder\Folder $_folder
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\FolderStructure[] $_folder_branch
 * @property-read int|null $_folder_branch_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\FolderViewTable[] $_pivot_tables
 * @property-read int|null $_pivot_tables_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereDefTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereLockPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereSideLeftFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereSideLeftMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereSideRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereSideTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderView whereUserLink($value)
 * @mixin \Eloquent
 */
class FolderView extends Model
{
    protected $table = 'folder_views';

    public $timestamps = false;

    protected $fillable = [
        'folder_id',
        'hash',
        'name',
        'notes',
        'user_link',
        'is_active',
        'is_locked',
        'lock_pass',
        'side_top',
        'side_left_menu',
        'side_left_filter',
        'side_right',
        'def_table_id',
    ];

    public function _folder() {
        return $this->belongsTo(Folder::class, 'folder_id', 'id');
    }

    public function _folder_branch() {
        return $this->hasMany(FolderStructure::class, 'element_id', 'folder_id');
    }

    public function _checked_tables() {
        return $this->belongsToMany(Table::class, 'folder_views_2_tables', 'folder_view_id', 'table_id');
    }

    public function _pivot_tables() {
        return $this->hasMany(FolderViewTable::class, 'folder_view_id', 'id');
    }
}
