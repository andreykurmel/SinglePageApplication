<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Pages\Pages;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\Folder
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $structure
 * @property string|null $description
 * @property int $is_opened
 * @property int $is_system
 * @property int $in_shared
 * @property int|null $is_folder_link
 * @property int|null $inside_folder_link
 * @property int|null $shared_from_id
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property string|null $icon_path
 * @property int|null $for_shared_user_id
 * @property int $menutree_order
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\FolderView[] $_folder_views
 * @property-read int|null $_folder_views_count
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\FolderStructure[] $_root_folders
 * @property-read int|null $_root_folders_count
 * @property-read \Vanguard\Models\Folder\FolderStructure|null $_structure_record
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\FolderStructure[] $_sub_folders
 * @property-read int|null $_sub_folders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\Folder2Table[] $_table_links
 * @property-read int|null $_table_links_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\Table[] $_tables
 * @property-read int|null $_tables_count
 * @property-read \Vanguard\User|null $_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup[] $_user_groups
 * @property-read int|null $_user_groups_count
 * @mixin \Eloquent
 * @property string|null $import_source
 * @property string|null $importfolder_airtable_save
 * @property string|null $importfolder_google_save
 * @property string|null $importfolder_dropbox_save
 * @property string|null $importfolder_onedrive_save
 * @property string|null $importfolder_ocr_save
 * @property string|null $importfolder_local_save
 */
class Folder extends Model
{
    protected $table = 'folders';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'structure',
        'description',
        'is_opened',
        'is_system',
        'in_shared',//0 - regular, 1 - in SHARED, 2 - in APPs
        'icon_path',
        'is_folder_link',
        'inside_folder_link',
        'shared_from_id',
        'for_shared_user_id',
        'menutree_order',
        'menutree_accordion_panel',

        'import_source',
        'importfolder_airtable_save',
        'importfolder_google_save',
        'importfolder_dropbox_save',
        'importfolder_onedrive_save',
        'importfolder_ocr_save',
        'importfolder_local_save',

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
            ->withPivot(['id', 'user_id', 'type', 'structure', 'is_folder_link']);
    }

    public function _pages() {
        return $this
            ->belongsToMany(Pages::class, 'folders_2_entities', 'folder_id', 'entity_id')
            ->where('entity_type', '=', 'page')
            ->as('link')
            ->withPivot(['id', 'user_id', 'entity_type', 'structure']);
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
