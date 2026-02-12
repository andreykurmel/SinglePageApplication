<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableChart
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string $chart_data_range
 * @property string|null $description
 * @property int $chart_active
 * @property int $bi_fix_layout
 * @property int $bi_can_add
 * @property int $bi_cell_height
 * @property int $bi_can_settings
 * @property int $bi_cell_spacing
 * @property int $bi_corner_radius
 * @property-read Table $_table
 * @property-read Collection|TableChart[] $_charts
 * @mixin Eloquent
 */
class TableChartTab extends Model
{
    protected $table = 'table_chart_tabs';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'chart_data_range',
        'description',
        'chart_active',
        'bi_fix_layout',
        'bi_can_add',
        'bi_can_settings',
        'bi_cell_height',
        'bi_cell_spacing',
        'bi_corner_radius',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _charts()
    {
        return $this->hasMany(TableChart::class, 'table_chart_tab_id', 'id');
    }
}
