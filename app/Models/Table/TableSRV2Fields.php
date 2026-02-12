<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;

/**
 *
 * @property int $id
 * @property int $table_data_requests_id
 * @property int $table_field_id
 * @property int $is_start_table_popup
 * @property int $is_table_field_in_popup
 * @property int $is_hdr_lvl_one_row
 * @property int $is_dcr_section
 * @property-read Table $_table
 * @property-read TableField $_field
 * @mixin \Eloquent
 */
class TableSRV2Fields extends Model
{
    protected $table = 'table_srv_2_table_fields';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'table_field_id',
        'width_of_table_popup',
        'is_start_table_popup',
        'is_table_field_in_popup',
        'is_hdr_lvl_one_row',
        'is_dcr_section',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_data_requests_id', 'id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
