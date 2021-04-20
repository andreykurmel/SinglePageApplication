<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableView;
use Vanguard\User;

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
