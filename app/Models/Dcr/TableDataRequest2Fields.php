<?php

namespace Vanguard\Models\Dcr;

use Illuminate\Database\Eloquent\Model;
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
 * @property string|null $dcr_section_name
 * @property int $is_topbot_in_popup
 * @property int $fld_popup_shown
 * @property int $fld_display_name
 * @property int $fld_display_value
 * @property int $fld_display_border
 * @property string $fld_display_header_type
 * @property-read TableDataRequest $_table_data_request
 * @property-read TableField $_field
 * @mixin \Eloquent
 */
class TableDataRequest2Fields extends Model
{
    protected $table = 'table_data_requests_2_table_fields';

    public $timestamps = false;

    protected $fillable = [
        'table_data_requests_id',
        'table_field_id',
        'width_of_table_popup',
        'is_start_table_popup',
        'is_table_field_in_popup',
        'is_hdr_lvl_one_row',
        'is_dcr_section',
        'dcr_section_name',
        'is_topbot_in_popup',
        'fld_popup_shown',
        'fld_display_name',
        'fld_display_value',
        'fld_display_border',
        'fld_display_header_type',
    ];


    public function _table_data_request() {
        return $this->belongsTo(TableDataRequest::class, 'table_data_requests_id', 'id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
