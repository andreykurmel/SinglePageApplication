<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\User;

class TableChart extends Model
{
    protected $table = 'table_charts';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'row_idx',
        'col_idx',
        'title',
        'chart_settings',
        'cached_data',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
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
