<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableView;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\FolderViewTable
 *
 * @property int $id
 * @property int $folder_view_id
 * @property int $table_id
 * @property int|null $assigned_view_id
 * @property-read \Vanguard\Models\Table\TableView|null $_assigned_view
 * @property-read \Vanguard\Models\Folder\FolderView $_folder_view
 * @property-read \Vanguard\Models\Table\Table $_table
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable whereAssignedViewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable whereFolderViewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Folder\FolderViewTable whereTableId($value)
 * @mixin \Eloquent
 */
class FolderViewTable extends Model
{
    protected $table = 'folder_views_2_tables';

    public $timestamps = false;

    protected $fillable = [
        'folder_view_id',
        'table_id',
        'assigned_view_id',
    ];


    public function _folder_view() {
        return $this->belongsTo(FolderView::class, 'folder_view_id', 'id');
    }

    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _assigned_view() {
        return $this->belongsTo(TableView::class, 'assigned_view_id', 'id');
    }
}
