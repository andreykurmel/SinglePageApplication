<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\User\UserPaymentKey;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableFieldLink
 *
 * @property int $id
 * @property int $table_field_id
 * @property string $name
 * @property int $lnk_header
 * @property string $link_type
 * @property string|null $link_display
 * @property string $link_pos
 * @property string $icon
 * @property string|null $tooltip
 * @property int $row_order
 * @property int|null $table_ref_condition_id
 * @property int|null $listing_field_id
 * @property int $listing_rows_width
 * @property int $listing_rows_min_width
 * @property int $board_view_height
 * @property int $board_title_width
 * @property int $board_image_width
 * @property int $board_image_height
 * @property int|null $board_image_fld_id
 * @property string|null $board_display_position
 * @property string $board_display_view
 * @property string $board_display_fit
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $table_app_id
 * @property string|null $address_field
 * @property int|null $address_field_id
 * @property int|null $multiple_web_label_fld_id
 * @property int|null $link_field_lat
 * @property int|null $link_field_lng
 * @property int|null $link_field_address
 * @property int|null $always_available
 * @property int $editability_rced_fields
 * @property int|null $lnk_dcr_permission_id
 * @property int|null $lnk_srv_permission_id
 * @property int|null $lnk_mrv_permission_id
 * @property string|null $web_prefix
 * @property int|null $hide_empty_web
 * @property int $popup_can_table
 * @property int $popup_can_list
 * @property int $popup_can_board
 * @property string|null $popup_display
 * @property int $pop_width_px
 * @property int $pop_width_px_min
 * @property int|null $pop_width_px_max
 * @property int $pop_height
 * @property int $pop_height_min
 * @property int|null $pop_height_max
 * @property string $listing_panel_status
 * @property float $listing_header_wi
 * @property int $show_sum
 * @property int $floating_action
 * @property string $table_def_align
 * @property int $table_fit_width
 * @property int|null $add_record_limit
 * @property int|null $already_added_records
 * @property string|null $link_preview_fields
 * @property string|null $email_addon_fields
 * @property int $link_preview_show_flds
 * @property int|null $payment_amount_fld_id
 * @property int|null $payment_history_payee_fld_id
 * @property int|null $payment_history_amount_fld_id
 * @property int|null $payment_history_date_fld_id
 * @property int|null $payment_method_fld_id
 * @property int|null $payment_paypal_keys_id
 * @property int|null $payment_stripe_keys_id
 * @property int|null $payment_description_fld_id
 * @property int|null $payment_customer_fld_id
 * @property int|null $history_fld_id
 * @property int|null $linked_report_id
 * @property string $inline_style
 * @property int|null $inline_in_vert_table
 * @property int|null $inline_is_opened
 * @property string $inline_width
 * @property int|null $inline_hide_tab
 * @property int|null $inline_hide_boundary
 * @property int|null $inline_hide_padding
 * @property int $max_height_in_vert_table
 * @property int|null $can_row_add
 * @property int|null $can_row_delete
 * @property string|null $avail_addons
 * @property int|null $share_mrv_id
 * @property int|null $share_url_field_id
 * @property int|null $share_custom_field_id
 * @property int|null $share_can_custom
 * @property int|null $share_custom_hash
 * @property int|null $share_web_link_id
 * @property int|null $share_record_link_id
 * @property int|null $share_is_web
 * @property int|null $eri_parser_file_id
 * @property int|null $eri_writer_file_id
 * @property int|null $eri_parser_link_id
 * @property int $eri_remove_prev_records
 * @property array|null $eri_writer_filename_fields
 * @property int $eri_writer_filename_year
 * @property int $eri_writer_filename_time
 * @property string|null $da_loading_type
 * @property int|null $da_loading_gemini_key_id
 * @property int|null $da_loading_image_field_id
 * @property int|null $da_loading_output_table_id
 * @property int $da_loading_remove_prev_rec
 * @property int|null $mto_dal_pdf_doc_field_id
 * @property int|null $mto_dal_pdf_output_table_id
 * @property int $mto_dal_pdf_remove_prev_rec
 * @property int|null $mto_geom_doc_field_id
 * @property int|null $mto_geom_output_table_id
 * @property int $mto_geom_remove_prev_rec
 * @property int|null $ai_extract_doc_field_id
 * @property int|null $ai_extract_ai_id
 * @property int|null $ai_extract_output_table_id
 * @property int $ai_extract_remove_prev_rec
 * @property string|null $link_export_drilled_fields
 * @property int $link_export_json_drill
 * @property int $json_import_field_id
 * @property int $json_export_field_id
 * @property int $json_export_filename_table
 * @property int $json_export_filename_link
 * @property array|null $json_export_filename_fields
 * @property int $json_export_filename_year
 * @property int $json_export_filename_time
 * @property int $json_auto_export
 * @property int $json_auto_remove_after_export
 * @property int|null $smart_select_source_field_id
 * @property int|null $smart_select_target_field_id
 * @property string $smart_select_data_range
 * @property \Vanguard\Models\Table\TableField|null $_address_field
 * @property \Vanguard\User|null $_created_user
 * @property \Vanguard\Models\Table\TableField $_field
 * @property \Vanguard\Models\Table\TableField|null $_listing_field
 * @property \Vanguard\Models\Table\TableField|null $_map_field_address
 * @property \Vanguard\Models\Table\TableField|null $_map_field_lat
 * @property \Vanguard\Models\Table\TableField|null $_map_field_lng
 * @property \Vanguard\User|null $_modified_user
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkParam[] $_params
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkColumn[] $_columns
 * @property int|null $_params_count
 * @property \Vanguard\Models\Table\TableField|null $_payment_amount_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_customer_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_description_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_history_amount_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_history_date_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_history_payee_fld
 * @property \Vanguard\Models\Table\TableField|null $_payment_method_fld
 * @property \Vanguard\Models\Table\TableField|null $_smart_source_fld
 * @property \Vanguard\Models\Table\TableField|null $_smart_target_fld
 * @property \Vanguard\Models\User\UserPaymentKey|null $_paypal_user_key
 * @property \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_ref_condition
 * @property \Vanguard\Models\User\UserPaymentKey|null $_stripe_user_key
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkToDcr[] $_to_dcrs
 * @property int|null $_to_dcrs_count
 * @property Table|null $_da_output_table
 * @property Table|null $_mto_dal_output_table
 * @property Table|null $_mto_geom_output_table
 * @property Table|null $_ai_extract_output_table
 * @property \Illuminate\Database\Eloquent\Collection|TableFieldLinkDaLoading[] $_link_app_correspondences
 * @property \Illuminate\Database\Eloquent\Collection|TableFieldLinkEriTable[] $_eri_tables
 * @property \Illuminate\Database\Eloquent\Collection|TableFieldLinkEriPart[] $_eri_parts
 * @mixin \Eloquent
 */
class TableFieldLink extends Model
{
    protected $table = 'table_field_links';

    public $timestamps = false;

    protected $fillable = [
        'table_field_id',
        'lnk_header',
        'name',
        'link_type', // 'Record', 'Web', 'App', 'GMap', 'GEarth', 'History', 'Add-on (Report)'
        'link_pos', // 'before', 'after'
        'icon',

        'link_display', // 'Popup','Table','RorT'
        'tooltip',
        'row_order',
        'popup_can_table',
        'popup_can_list',
        'popup_can_board',
        'popup_display', // 'Table', 'Listing', 'Boards'
        'pop_width_px',
        'pop_width_px_min',
        'pop_width_px_max',
        'pop_height',
        'pop_height_min',
        'pop_height_max',
        'listing_panel_status',
        'listing_header_wi',
        'show_sum',
        'floating_action',
        'table_def_align',
        'table_fit_width',
        'table_ref_condition_id',
        'listing_field_id',
        'listing_rows_width',
        'listing_rows_min_width',
        'board_view_height',
        'board_display_position',
        'board_display_view',
        'board_display_fit',
        'board_image_fld_id',
        'board_title_width',
        'board_image_width',
        'board_image_height',
        'address_field_id',
        'multiple_web_label_fld_id',
        'table_app_id',
        'link_field_lat',
        'link_field_lng',
        'link_field_address',
        'always_available',
        'editability_rced_fields',
        'lnk_dcr_permission_id',
        'lnk_srv_permission_id',
        'lnk_mrv_permission_id',
        'web_prefix',
        'hide_empty_web',
        'add_record_limit',
        'already_added_records',
        'link_preview_fields',
        'link_preview_show_flds',
        'email_addon_fields',
        'history_fld_id',
        'linked_report_id',
        'inline_style',//'regular', 'simple'
        'inline_in_vert_table',
        'inline_is_opened',
        'inline_width',//'full', 'field'
        'inline_hide_tab',
        'inline_hide_boundary',
        'inline_hide_padding',
        'max_height_in_vert_table',
        'can_row_add',
        'can_row_delete',
        'avail_addons',
        'eri_parser_file_id',
        'eri_writer_file_id',
        'eri_parser_link_id',
        'eri_remove_prev_records',
        'eri_writer_filename_fields',
        'eri_writer_filename_year',
        'eri_writer_filename_time',
        'da_loading_type',
        'da_loading_gemini_key_id',
        'da_loading_image_field_id',
        'da_loading_output_table_id',
        'da_loading_remove_prev_rec',
        'mto_dal_pdf_doc_field_id',
        'mto_dal_pdf_output_table_id',
        'mto_dal_pdf_remove_prev_rec',
        'mto_geom_doc_field_id',
        'mto_geom_output_table_id',
        'mto_geom_remove_prev_rec',
        'ai_extract_doc_field_id',
        'ai_extract_ai_id',
        'ai_extract_output_table_id',
        'ai_extract_remove_prev_rec',
        'share_mrv_id',
        'share_url_field_id',
        'share_custom_field_id',
        'share_can_custom',
        'share_custom_hash',
        'share_web_link_id',
        'share_record_link_id',
        'share_is_web',
        'link_export_json_drill',
        'link_export_drilled_fields',
        'json_import_field_id',
        'json_export_field_id',
        'json_export_filename_table',
        'json_export_filename_link',
        'json_export_filename_fields',
        'json_export_filename_year',
        'json_export_filename_time',
        'json_auto_export',
        'json_auto_remove_after_export',
        'smart_select_source_field_id',
        'smart_select_target_field_id',
        'smart_select_data_range',

        'payment_method_fld_id', //'stripe','paypal'
        'payment_paypal_keys_id',
        'payment_stripe_keys_id',
        'payment_description_fld_id',
        'payment_amount_fld_id',
        'payment_customer_fld_id',
        'payment_history_payee_fld_id',
        'payment_history_amount_fld_id',
        'payment_history_date_fld_id',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    protected $casts = [
        'json_export_filename_fields' => 'array',
        'eri_writer_filename_fields' => 'array',
    ];

    /**
     * @return int|null
     */
    public function getCustomField()
    {
        return $this->share_custom_hash ? $this->share_custom_field_id : null;
    }


    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _ref_condition() {
        return $this->hasOne(TableRefCondition::class, 'id', 'table_ref_condition_id');
    }

    public function _listing_field() {
        return $this->hasOne(TableField::class, 'id', 'listing_field_id');
    }

    public function _address_field() {
        return $this->hasOne(TableField::class, 'id', 'address_field_id');
    }

    public function _map_field_lat() {
        return $this->hasOne(TableField::class, 'id', 'link_field_lat');
    }

    public function _map_field_lng() {
        return $this->hasOne(TableField::class, 'id', 'link_field_lng');
    }

    public function _map_field_address() {
        return $this->hasOne(TableField::class, 'id', 'link_field_address');
    }

    public function _params() {
        return $this->hasMany(TableFieldLinkParam::class, 'table_field_link_id', 'id');
    }

    public function _columns() {
        return $this->hasMany(TableFieldLinkColumn::class, 'table_link_id', 'id');
    }

    public function _eri_tables() {
        return $this->hasMany(TableFieldLinkEriTable::class, 'table_link_id', 'id');
    }

    public function _eri_parts() {
        return $this->hasMany(TableFieldLinkEriPart::class, 'table_link_id', 'id');
    }

    public function _da_output_table() {
        return $this->hasOne(Table::class, 'id', 'da_loading_output_table_id');
    }

    public function _mto_dal_output_table() {
        return $this->hasOne(Table::class, 'id', 'mto_dal_pdf_output_table_id');
    }

    public function _mto_geom_output_table() {
        return $this->hasOne(Table::class, 'id', 'mto_geom_output_table_id');
    }

    public function _ai_extract_output_table() {
        return $this->hasOne(Table::class, 'id', 'ai_extract_output_table_id');
    }

    public function _link_app_correspondences() {
        return $this->hasMany(TableFieldLinkDaLoading::class, 'table_link_id', 'id');
    }

    public function _to_dcrs() {
        return $this->hasMany(TableFieldLinkToDcr::class, 'table_field_link_id', 'id');
    }

    public function _payment_method_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_method_fld_id');
    }
    public function _paypal_user_key() {
        return $this->hasOne(UserPaymentKey::class, 'id', 'payment_paypal_keys_id');
    }
    public function _stripe_user_key() {
        return $this->hasOne(UserPaymentKey::class, 'id', 'payment_stripe_keys_id');
    }
    public function _payment_customer_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_customer_fld_id');
    }
    public function _payment_amount_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_amount_fld_id');
    }
    public function _payment_description_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_description_fld_id');
    }
    public function _payment_history_payee_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_payee_fld_id');
    }
    public function _payment_history_amount_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_amount_fld_id');
    }
    public function _payment_history_date_fld() {
        return $this->hasOne(TableField::class, 'id', 'payment_history_date_fld_id');
    }
    public function _smart_source_fld() {
        return $this->hasOne(TableField::class, 'id', 'smart_select_source_field_id');
    }
    public function _smart_target_fld() {
        return $this->hasOne(TableField::class, 'id', 'smart_select_target_field_id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
