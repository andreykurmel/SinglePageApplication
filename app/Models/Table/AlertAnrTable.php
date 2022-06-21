<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\AlertAnrTable
 *
 * @property int $id
 * @property int $table_alert_id
 * @property string|null $name
 * @property int|null $table_id
 * @property int $qty
 * @property int $is_active
 * @property int $need_approve
 * @property string|null $triggered_row
 * @property int $temp_is_active
 * @property string|null $temp_name
 * @property int|null $temp_table_id
 * @property int|null $temp_qty
 * @property int|null $approve_user
 * @property-read \Vanguard\Models\Table\TableAlert $_alert
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertAnrTableField[] $_anr_fields
 * @property-read int|null $_anr_fields_count
 * @property-read \Vanguard\Models\Table\Table|null $_table
 * @property-read \Vanguard\Models\Table\Table|null $_temp_table
 * @mixin \Eloquent
 */
class AlertAnrTable extends Model
{
    protected $table = 'alert_anr_tables';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'name',
        'table_id',
        'qty',
        'is_active',
        'need_approve',
        'approve_user',
        //cols for temp
        'temp_is_active',
        'triggered_row',
        'temp_name',
        'temp_table_id',
        'temp_qty',
    ];


    public function _alert() {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }

    public function _table() {
        return $this->hasOne(Table::class, 'id', 'table_id');
    }
    public function _temp_table() {
        return $this->hasOne(Table::class, 'id', 'temp_table_id');
    }

    public function _anr_fields() {
        return $this->hasMany(AlertAnrTableField::class, 'anr_table_id', 'id');
    }
}
