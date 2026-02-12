<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableChart
 *
 * @property int $id
 * @property int|null $table_chart_tab_id
 * @property int $table_id
 * @property int $user_id
 * @property int $col_idx
 * @property int $row_idx
 * @property string $chart_settings
 * @property string|null $cached_data
 * @property string|null $title
 * @property string|null $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableChartRight[] $_chart_rights
 * @property-read int|null $_chart_rights_count
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read TableChartTab $_tab
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property-read int|null $_table_permissions_count
 * @mixin \Eloquent
 */
class TableChart extends Model
{
    protected $table = 'table_charts';

    public $timestamps = false;

    protected $fillable = [
        'table_chart_tab_id',
        'table_id',
        'user_id',
        'row_idx',
        'col_idx',
        'name',
        'title',
        'chart_settings',
        'cached_data',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _tab() {
        return $this->belongsTo(TableChartTab::class, 'table_chart_tab_id', 'id');
    }

    public function _chart_rights() {
        return $this->hasMany(TableChartRight::class, 'table_chart_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_chart_rights', 'table_chart_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_chart_id', 'table_permission_id', 'can_edit']);
    }
}
