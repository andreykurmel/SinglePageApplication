<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableSnapshotField
 *
 * @property int $id
 * @property int $table_alert_id
 * @property int $current_field_id
 * @property int $source_field_id
 * @property-read TableAlert $_alert
 * @property-read TableField $_cur_field
 * @property-read TableField $_source_field
 * @mixin Eloquent
 */
class TableAlertSnapshotField extends Model
{
    protected $table = 'table_alert_snapshot_fields';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'current_field_id',
        'source_field_id',
    ];


    public function _alert()
    {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }

    public function _cur_field()
    {
        return $this->belongsTo(TableField::class, 'current_field_id', 'id');
    }

    public function _source_field()
    {
        return $this->belongsTo(TableField::class, 'source_field_id', 'id');
    }
}
