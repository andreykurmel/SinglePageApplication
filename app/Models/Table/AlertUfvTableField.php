<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\AlertUfvTableField
 *
 * @property int $id
 * @property int $ufv_table_id
 * @property int|null $table_field_id
 * @property string|null $source
 * @property string|null $input
 * @property string|null $show_input
 * @property int|null $inherit_field_id
 * @property-read \Vanguard\Models\Table\TableField|null $_field
 * @property-read \Vanguard\Models\Table\TableField|null $_inherit_field
 * @property-read \Vanguard\Models\Table\AlertUfvTable $_ufv
 * @mixin \Eloquent
 */
class AlertUfvTableField extends Model
{
    protected $table = 'alert_ufv_table_fields';

    public $timestamps = false;

    protected $fillable = [
        'ufv_table_id',
        'table_field_id',
        'source', // 'Input','Inherit'
        'input',
        'show_input',
        'inherit_field_id',
    ];


    public function _ufv() {
        return $this->belongsTo(AlertUfvTable::class, 'ufv_table_id', 'id');
    }

    public function _field() {
        return $this->hasOne(TableField::class, 'id', 'table_field_id');
    }

    public function _inherit_field() {
        return $this->hasOne(TableField::class, 'id', 'inherit_field_id');
    }
}
