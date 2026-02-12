<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_report_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableReport $_report
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableReportRight extends Model
{
    protected $table = 'table_report_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_report_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _report()
    {
        return $this->belongsTo(TableReport::class, 'table_report_id', 'id');
    }
}
