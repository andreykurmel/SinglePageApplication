<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\AlertAnrTableField
 *
 * @property int $id
 * @property int $anr_table_id
 * @property int|null $table_field_id
 * @property string|null $source
 * @property string|null $input
 * @property string|null $show_input
 * @property int|null $inherit_field_id
 * @property int|null $temp_table_field_id
 * @property string|null $temp_source
 * @property string|null $temp_input
 * @property string|null $show_temp_input
 * @property int|null $temp_inherit_field_id
 * @property-read \Vanguard\Models\Table\AlertAnrTable $_anr
 * @property-read \Vanguard\Models\Table\TableField|null $_field
 * @property-read \Vanguard\Models\Table\TableField|null $_inherit_field
 * @property-read \Vanguard\Models\Table\TableField|null $_temp_field
 * @property-read \Vanguard\Models\Table\TableField|null $_temp_inherit_field
 * @mixin \Eloquent
 */
class AlertAnrTableField extends Model
{
    protected $table = 'alert_anr_table_fields';

    public $timestamps = false;

    protected $fillable = [
        'anr_table_id',
        'table_field_id',
        'source', // 'Input','Inherit'
        'input',
        'show_input',
        'inherit_field_id',
        //cols for temp
        'temp_table_field_id',
        'temp_source', // 'Input','Inherit'
        'temp_input',
        'show_temp_input',
        'temp_inherit_field_id',
    ];


    public function _anr() {
        return $this->belongsTo(AlertAnrTable::class, 'anr_table_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }

    public function _inherit_field() {
        return $this->hasOne(TableField::class, 'id', 'inherit_field_id');
    }

    public function _temp_field() {
        return $this->hasOne(TableField::class, 'id', 'temp_table_field_id');
    }

    public function _temp_inherit_field() {
        return $this->hasOne(TableField::class, 'id', 'temp_inherit_field_id');
    }
}
