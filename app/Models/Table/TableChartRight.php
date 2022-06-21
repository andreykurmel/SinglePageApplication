<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_chart_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read \Vanguard\Models\Table\TableChart $_chart
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @mixin \Eloquent
 */
class TableChartRight extends Model
{
    protected $table = 'table_chart_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_chart_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _chart() {
        return $this->belongsTo(TableChart::class, 'table_chart_id', 'id');
    }
}
