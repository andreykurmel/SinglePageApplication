<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableGantt
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string $gantt_data_range
 * @property string|null $gantt_info_type
 * @property int|null $gantt_navigation
 * @property int|null $gantt_navigation_bottom
 * @property int $gantt_navigator_height
 * @property int $gantt_row_height
 * @property int|null $gantt_show_names
 * @property int|null $gantt_highlight
 * @property string|null $gantt_day_format
 * @property int $gantt_active
 * @property string|null $description
 * @property Table $_table
 * @property TableGanttSetting[] $_specifics
 * @property Collection|TableGanttRight[] $_gantt_rights
 * @mixin \Eloquent
 */
class TableGantt extends Model
{
    protected $table = 'table_gantts';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'gantt_data_range',
        'gantt_info_type',
        'gantt_navigation',
        'gantt_navigation_bottom',
        'gantt_navigator_height',
        'gantt_row_height',
        'gantt_show_names',
        'gantt_highlight',
        'gantt_day_format',
        'gantt_active',
        'description',
    ];



    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_gantt_rights', 'table_gantt_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_gantt_id', 'table_permission_id', 'can_edit']);
    }
    public function _gantt_rights() {
        return $this->hasMany(TableGanttRight::class, 'table_gantt_id', 'id');
    }

    public function _specifics() {
        return $this->hasMany(TableGanttSetting::class, 'table_gantt_id', 'id');
    }
}
