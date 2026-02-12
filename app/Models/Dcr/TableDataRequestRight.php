<?php

namespace Vanguard\Models\Dcr;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_data_request_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableDataRequest $_dcr
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @mixin \Eloquent
 */
class TableDataRequestRight extends Model
{
    protected $table = 'table_data_request_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_data_request_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _dcr() {
        return $this->belongsTo(TableDataRequest::class, 'table_data_request_id', 'id');
    }
}
