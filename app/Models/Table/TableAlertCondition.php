<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableAlertCondition
 *
 * @property int $id
 * @property int $table_alert_id
 * @property int $table_field_id
 * @property string|null $condition
 * @property string|null $logic
 * @property string|null $new_value
 * @property int $is_active
 * @property-read \Vanguard\Models\Table\TableAlert $_alert
 * @property-read \Vanguard\Models\Table\TableField|null $_field
 * @mixin \Eloquent
 */
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
