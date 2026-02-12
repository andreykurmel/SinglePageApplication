<?php

namespace Vanguard\Models\Dcr;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;

/**
 * Vanguard\Models\Dcr\DcrLinkedTable
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property int $table_request_id
 * @property int $linked_table_id
 * @property int|null $linked_permission_id
 * @property int|null $position_field_id
 * @property int|null $passed_ref_cond_id
 * @property string|null $header
 * @property string|null $position
 * @property string|null $style
 * @property string $default_display
 * @property int $embd_table
 * @property int $embd_listing
 * @property int $embd_board
 * @property int|null $embd_stats
 * @property int|null $embd_fit_width
 * @property string $embd_table_align
 * @property int|null $embd_float_actions
 * @property string|null $placement_tab_name
 * @property int|null $placement_tab_order
 * @property int|null $listing_field_id
 * @property float $listing_rows_width
 * @property float $listing_rows_min_width
 * @property int $board_view_height
 * @property int $board_title_width
 * @property int $board_image_width
 * @property int $board_image_height
 * @property int|null $board_image_fld_id
 * @property string|null $board_display_position
 * @property string $board_display_view
 * @property string $board_display_fit
 * @property int $ctlg_is_active
 * @property int $ctlg_columns_number
 * @property string $ctlg_data_range
 * @property int|null $ctlg_table_id
 * @property int|null $ctlg_distinct_field_id
 * @property int|null $ctlg_parent_link_field_id
 * @property int|null $ctlg_parent_quantity_field_id
 * @property array|null $ctlg_visible_field_ids
 * @property array|null $ctlg_filter_field_ids
 * @property string $ctlg_display_option
 * @property int $ctlg_board_view_height
 * @property int $ctlg_board_title_width
 * @property int $ctlg_board_image_width
 * @property int $ctlg_board_image_height
 * @property int|null $ctlg_board_image_fld_id
 * @property string|null $ctlg_board_display_position
 * @property string $ctlg_board_display_view
 * @property string $ctlg_board_display_fit
 * @property-read TablePermission|null $_linked_permission
 * @property-read Table $_linked_table
 * @property-read TableRefCondition|null $_passed_ref_cond
 * @property-read TableField|null $_position_field
 * @property-read TableField|null $_ctlg_distinct_field
 * @property-read TableDataRequest $_table_data_request
 * @mixin Eloquent
 */
class DcrLinkedTable extends Model
{
    public $timestamps = false;

    protected $table = 'dcr_linked_tables';

    protected $fillable = [
        'is_active',
        'name',
        'table_request_id',
        'linked_table_id',
        'linked_permission_id',
        'position_field_id',
        'passed_ref_cond_id',
        'header',
        'position',
        'style',//['Default','Top/Bot']
        'default_display',//['Table', 'Listing', 'Boards']
        'max_nbr_rcds_embd',
        'max_height_inline_embd',
        'embd_table',
        'embd_listing',
        'embd_board',
        'embd_stats',
        'embd_fit_width',
        'embd_table_align',
        'embd_float_actions',
        'placement_tab_name',
        'placement_tab_order',
        'listing_field_id',
        'listing_rows_width',
        'listing_rows_min_width',

        'ctlg_is_active',
        'ctlg_columns_number',
        'ctlg_data_range',
        'ctlg_table_id',
        'ctlg_distinct_field_id',
        'ctlg_parent_link_field_id',
        'ctlg_parent_quantity_field_id',
        'ctlg_visible_field_ids',
        'ctlg_filter_field_ids',
        'ctlg_display_option',
        'ctlg_board_view_height',
        'ctlg_board_title_width',
        'ctlg_board_image_width',
        'ctlg_board_image_height',
        'ctlg_board_image_fld_id',
        'ctlg_board_display_position',
        'ctlg_board_display_view',
        'ctlg_board_display_fit',
    ];

    protected $casts = [
        'ctlg_visible_field_ids' => 'array',
        'ctlg_filter_field_ids' => 'array',
    ];

    public function _table_data_request()
    {
        return $this->belongsTo(TableDataRequest::class, 'table_request_id', 'id');
    }

    public function _linked_table()
    {
        return $this->belongsTo(Table::class, 'linked_table_id', 'id');
    }

    public function _linked_permission()
    {
        return $this->belongsTo(TablePermission::class, 'linked_permission_id', 'id');
    }

    public function _passed_ref_cond()
    {
        return $this->belongsTo(TableRefCondition::class, 'passed_ref_cond_id', 'id');
    }

    public function _position_field()
    {
        return $this->belongsTo(TableField::class, 'position_field_id', 'id');
    }

    public function _ctlg_distinct_field()
    {
        return $this->belongsTo(TableField::class, 'ctlg_distinct_field_id', 'id');
    }
}
