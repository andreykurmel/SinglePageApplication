<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TableFieldLinkParam extends Model
{
    protected $table = 'table_field_link_params';

    public $timestamps = false;

    protected $fillable = [
        'table_field_link_id',
        'param',
        'value',
        'column_id',
    ];


    public function _field() {
        return $this->belongsTo(TableFieldLink::class, 'table_field_link_id', 'id');
    }
}
