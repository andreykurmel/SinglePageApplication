<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

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
