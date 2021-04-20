<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

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
