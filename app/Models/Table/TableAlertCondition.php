<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\User;

class TableAlertCondition extends Model
{
    protected $table = 'table_alert_conditions';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'table_field_id',
        'logic',
        'new_value',
        'condition',
        'is_active',
    ];


    public function _alert() {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }
}
