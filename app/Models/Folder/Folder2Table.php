<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\Folder2Table
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property int|null $folder_id
 * @property string $type
 * @property string $structure
 * @property int|null $is_folder_link
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereStructure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\Folder2Table whereUserId($value)
 * @mixin \Eloquent
 */
class Folder2Table extends Model
{
    protected $table = 'folders_2_tables';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'folder_id',
        'type',
        'structure',
        'is_folder_link',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _folder() {
        return $this->belongsTo(Folder::class, 'folder_id', 'id')
            ->join('folder_trees as ft', function($q) {
                $q->whereRaw('ft.element_id = folders.id');
                $q->whereRaw('ft.child_id = folders.id');
            })
            ->selectRaw('folders.*, ft.parent_id');
    }

    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
