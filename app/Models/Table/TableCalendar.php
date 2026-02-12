<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableAlert
 *
 * @property int $id
 * @property int $table_id
 * @property string $calendar_data_range
 * @property string $name
 * @property string|null $description
 * @property string|null $calendar_locale
 * @property string|null $calendar_timezone
 * @property string $calendar_init_date
 * @property int $calendar_active
 * @property int|null $fldid_calendar_start
 * @property int|null $fldid_calendar_end
 * @property int|null $fldid_calendar_title
 * @property int|null $fldid_calendar_cond_format
 * @property Table $_table
 * @property Collection|TableCalendarRight[] $_calendar_rights
 * @mixin \Eloquent
 */
class TableCalendar extends Model
{
    protected $table = 'table_calendars';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'description',
        'calendar_data_range',
        'calendar_locale',
        'calendar_timezone',
        'calendar_init_date',
        'calendar_active',
        'fldid_calendar_start',
        'fldid_calendar_end',
        'fldid_calendar_title',
        'fldid_calendar_cond_format',
    ];



    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_calendar_rights', 'table_calendar_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_calendar_id', 'table_permission_id', 'can_edit']);
    }
    public function _calendar_rights() {
        return $this->hasMany(TableCalendarRight::class, 'table_calendar_id', 'id');
    }
}
