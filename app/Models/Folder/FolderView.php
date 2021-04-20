<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

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
