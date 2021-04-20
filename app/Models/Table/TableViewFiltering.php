<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TableViewFiltering extends Model
{
    protected $table = 'table_view_filtering';

    public $timestamps = false;

    protected $fillable = [
        'table_view_id',
        'active',
        'field_id',
        'criteria',
        'input_only',
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'field_id', 'id');
    }

    public function _view() {
        return $this->belongsTo(TableView::class, 'table_view_id', 'id');
    }
}
