<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\AlertUfvTableField
 *
 * @property int $id
 * @property int $table_alert_id
 * @property int $table_field_id
 * @property string|null $new_value
 * @property-read TableAlert|null $_alert
 * @property-read TableField|null $_field
 * @mixin Eloquent
 */
class AlertClickUpdate extends Model
{
    protected $table = 'table_alert_click_updates';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'table_field_id',
        'new_value',
    ];


    public function _alert()
    {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }

    public function _field()
    {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }
}
