<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableRowGroupRegular extends Model
{
    protected $table = 'table_row_group_regulars';

    public $timestamps = false;

    protected $fillable = [
        'table_row_group_id',
        'list_field',
        'field_value',
        'row_json'
    ];


    public function _row_group() {
        return $this->belongsTo(TableRowGroup::class, 'table_row_group_id', 'id');
    }
}
