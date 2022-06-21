<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;

/**
 * Vanguard\Models\Table\AlertUfvTable
 *
 * @property int $id
 * @property int $table_alert_id
 * @property string|null $name
 * @property int|null $table_id
 * @property int|null $table_ref_cond_id
 * @property int $is_active
 * @property-read \Vanguard\Models\Table\TableAlert $_alert
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_cond
 * @property-read \Vanguard\Models\Table\Table|null $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertUfvTableField[] $_ufv_fields
 * @property-read int|null $_ufv_fields_count
 * @mixin \Eloquent
 */
class AlertUfvTable extends Model
{
    protected $table = 'alert_ufv_tables';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'name',
        'table_id',
        'table_ref_cond_id',
        'is_active',
    ];


    public function _alert() {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }

    public function _table() {
        return $this->hasOne(Table::class, 'id', 'table_id');
    }
    public function _ref_cond() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_cond_id');
    }

    public function _ufv_fields() {
        return $this->hasMany(AlertUfvTableField::class, 'ufv_table_id', 'id');
    }
}
