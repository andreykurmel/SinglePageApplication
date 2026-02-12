<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_calendar_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableGantt $_calendar
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableCalendarRight extends Model
{
    protected $table = 'table_calendar_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_calendar_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _calendar()
    {
        return $this->belongsTo(TableCalendar::class, 'table_calendar_id', 'id');
    }
}
