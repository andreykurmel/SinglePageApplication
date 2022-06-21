<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableFieldLinkToDcr
 *
 * @property int $id
 * @property int $table_field_link_id
 * @property int $table_dcr_id
 * @property int $status
 * @property int $add_limit
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_dcr
 * @property-read \Vanguard\Models\Table\TableFieldLink $_link
 * @mixin \Eloquent
 */
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
