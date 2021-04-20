<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

class TableFieldLinkToDcr extends Model
{
    protected $table = 'table_field_link_to_dcr';

    public $timestamps = false;

    protected $fillable = [
        'table_field_link_id',
        'table_dcr_id',
        'status',
        'add_limit',
    ];


    public function _link() {
        return $this->belongsTo(TableFieldLink::class, 'table_field_link_id', 'id');
    }

    public function _dcr() {
        return $this->belongsTo(TablePermission::class, 'table_dcr_id', 'id');
    }
}
