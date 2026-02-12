<?php

namespace Vanguard\Models\Dcr;

use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\Dcr\TableDataRequest
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property int $active
 * @property string|null $name
 * @property string|null $description
 * @property int $is_template
 * @property string|null $link_hash
 * @property string|null $custom_url
 * @property string|null $dcr_hash
 * @property string|null $pass
 * @property string|null $qr_link
 * @property int|null $permission_dcr_id
 * @property string $dcr_sec_background_by
 * @property string $dcr_sec_scroll_style
 * @property int $dcr_sec_slide_top_header
 * @property int $dcr_flow_header_stick
 * @property int $dcr_sec_slide_progresbar
 * @property int $dcr_sec_line_top
 * @property int $dcr_sec_line_bot
 * @property string|null $dcr_sec_line_color
 * @property int $dcr_sec_line_thick
 * @property string|null $dcr_sec_bg_top
 * @property string|null $dcr_sec_bg_bot
 * @property string|null $dcr_sec_bg_img
 * @property string $dcr_sec_bg_img_fit
 * @property int|null $dcr_form_line_height
 * @property int|null dcr_form_font_size
 * @property string|null $dcr_title
 * @property int|null $dcr_title_width
 * @property int|null $dcr_title_height
 * @property string|null $dcr_title_font_type
 * @property int|null $dcr_title_font_size
 * @property string|null $dcr_title_font_color
 * @property string|null $dcr_title_font_style
 * @property string|null $dcr_title_bg_img
 * @property string|null $dcr_title_bg_fit
 * @property string|null $dcr_title_bg_color
 * @property string $dcr_title_background_by
 * @property string $dcr_form_line_type
 * @property int $dcr_form_line_top
 * @property int $dcr_form_line_bot
 * @property int $dcr_form_line_thick
 * @property int $dcr_form_line_radius
 * @property string|null $dcr_form_line_color
 * @property string|null $dcr_form_bg_color
 * @property int $dcr_form_transparency
 * @property string|null $dcr_form_message
 * @property string|null $dcr_form_message_font
 * @property int|null $dcr_form_message_size
 * @property string|null $dcr_form_message_color
 * @property string|null $dcr_form_message_style
 * @property int|null $dcr_form_width
 * @property int $dcr_form_shadow
 * @property string|null $dcr_form_shadow_color
 * @property string $dcr_form_shadow_dir
 * @property int $download_pdf
 * @property int $download_png
 * @property int $one_per_submission
 * @property int|null $dcr_record_status_id
 * @property int|null $dcr_record_url_field_id
 * @property int|null $dcr_record_allow_unfinished
 * @property int|null $dcr_record_visibility_id
 * @property int|null $dcr_record_editability_id
 * @property int|null $dcr_record_visibility_def
 * @property int|null $dcr_record_editability_def
 * @property int|null $dcr_record_save_visibility_def
 * @property int|null $dcr_record_save_editability_def
 * @property int|null $stored_row_protection
 * @property int|null $stored_row_pass_id
 * @property int $dcr_active_notif
 * @property string|null $dcr_confirm_msg
 * @property string|null $dcr_unique_msg
 * @property int|null $dcr_email_field_id
 * @property int|null $dcr_cc_email_field_id
 * @property int|null $dcr_bcc_email_field_id
 * @property string|null $dcr_email_field_static
 * @property string|null $dcr_cc_email_field_static
 * @property string|null $dcr_bcc_email_field_static
 * @property string|null $dcr_email_subject
 * @property string|null $dcr_email_message
 * @property string $dcr_email_format
 * @property int|null $dcr_email_col_group_id
 * @property int|null $dcr_addressee_field_id
 * @property string|null $dcr_addressee_txt
 * @property string|null $dcr_signature_txt
 * @property int|null $dcr_save_email_field_id
 * @property int|null $dcr_save_cc_email_field_id
 * @property int|null $dcr_save_bcc_email_field_id
 * @property int|null $dcr_save_addressee_field_id
 * @property int|null $dcr_save_email_col_group_id
 * @property string|null $dcr_save_signature_txt
 * @property int $dcr_save_active_notif
 * @property string|null $dcr_save_confirm_msg
 * @property string|null $dcr_save_unique_msg
 * @property string|null $dcr_save_email_field_static
 * @property string|null $dcr_save_cc_email_field_static
 * @property string|null $dcr_save_bcc_email_field_static
 * @property string|null $dcr_save_email_subject
 * @property string|null $dcr_save_addressee_txt
 * @property string|null $dcr_save_email_message
 * @property string $dcr_save_email_format
 * @property int|null $dcr_upd_email_field_id
 * @property int|null $dcr_upd_cc_email_field_id
 * @property int|null $dcr_upd_bcc_email_field_id
 * @property int|null $dcr_upd_addressee_field_id
 * @property int|null $dcr_upd_email_col_group_id
 * @property string|null $dcr_upd_signature_txt
 * @property int $dcr_upd_active_notif
 * @property string|null $dcr_upd_confirm_msg
 * @property string|null $dcr_upd_unique_msg
 * @property string|null $dcr_upd_email_field_static
 * @property string|null $dcr_upd_cc_email_field_static
 * @property string|null $dcr_upd_bcc_email_field_static
 * @property string|null $dcr_upd_email_subject
 * @property string|null $dcr_upd_addressee_txt
 * @property string|null $dcr_upd_email_message
 * @property string $dcr_upd_email_format
 * @property float $dcr_many_rows_width
 * @property int|null $dcr_qr_with_name
 * @property int $dcr_accordion_single_open
 * @property string|null $dcr_tab_font_type
 * @property int|null $dcr_tab_font_size
 * @property string|null $dcr_tab_font_color
 * @property string|null $dcr_tab_font_style
 * @property string|null $dcr_tab_bg_color
 * @property int $dcr_tab_height
 * @property-read Collection|\Vanguard\Models\DataSetPermissions\TableDataRequest2Fields[] $_fields_pivot
 * @property-read Collection|TableColumnGroup[] $_column_groups
 * @property-read Collection|TableDataRequestColumn[] $_data_request_columns
 * @property-read TableField|null $_dcr_addressee_field
 * @property-read TableField|null $_dcr_bcc_email_field
 * @property-read TableField|null $_dcr_cc_email_field
 * @property-read TableColumnGroup|null $_dcr_email_col_group
 * @property-read TableField|null $_dcr_email_field
 * @property-read Collection|DcrLinkedTable[] $_dcr_linked_tables
 * @property-read TableField|null $_dcr_save_addressee_field
 * @property-read TableField|null $_dcr_save_bcc_email_field
 * @property-read TableField|null $_dcr_save_cc_email_field
 * @property-read TableColumnGroup|null $_dcr_save_email_col_group
 * @property-read TableField|null $_dcr_save_email_field
 * @property-read TableField|null $_dcr_upd_addressee_field
 * @property-read TableField|null $_dcr_upd_bcc_email_field
 * @property-read TableField|null $_dcr_upd_cc_email_field
 * @property-read TableField|null $_dcr_record_url_field
 * @property-read TableColumnGroup|null $_dcr_upd_email_col_group
 * @property-read TableField|null $_dcr_upd_email_field
 * @property-read TableField|null $_dcr_pass_field
 * @property-read Collection|TableDataRequestDefaultField[] $_default_fields
 * @property-read Table $_table
 * @mixin Eloquent
 */
class TableDataRequest extends Model
{
    protected $table = 'table_data_requests';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'active',
        'name',
        'link_hash',
        'custom_url',
        'pass',
        'dcr_hash',
        'qr_link',
        'is_template',
        'row_request',
        'description',
        'permission_dcr_id',
        'dcr_many_rows_width',
        'dcr_qr_with_name',
        'dcr_accordion_single_open',
        'dcr_tab_font_type',
        'dcr_tab_font_size',
        'dcr_tab_font_color',
        'dcr_tab_font_style',
        'dcr_tab_bg_color',
        'dcr_tab_height',

        'dcr_sec_background_by',
        'dcr_sec_scroll_style',
        'dcr_flow_header_stick',
        'dcr_sec_slide_top_header',
        'dcr_sec_slide_progresbar',
        'dcr_sec_line_top',
        'dcr_sec_line_bot',
        'dcr_sec_line_color',
        'dcr_sec_line_thick',
        'dcr_sec_bg_top',
        'dcr_sec_bg_bot',
        'dcr_sec_bg_img',
        'dcr_sec_bg_img_fit',

        'dcr_title',
        'dcr_title_width',
        'dcr_title_height',
        'dcr_title_font_type',
        'dcr_title_font_size',
        'dcr_title_font_color',
        'dcr_title_font_style',
        'dcr_title_bg_color',
        'dcr_title_bg_img',
        'dcr_title_bg_fit',
        'dcr_title_background_by',

        'dcr_form_line_type',// ['line','space']
        'dcr_form_line_top',
        'dcr_form_line_bot',
        'dcr_form_line_thick',
        'dcr_form_line_radius',
        'dcr_form_line_color',
        'dcr_form_bg_color',
        'dcr_form_transparency',
        'dcr_form_message',
        'dcr_form_message_font',
        'dcr_form_message_size',
        'dcr_form_message_color',
        'dcr_form_message_style',
        'dcr_form_width',
        'dcr_form_shadow',
        'dcr_form_shadow_color',
        'dcr_form_shadow_dir',
        'dcr_form_line_height',
        'dcr_form_font_size',

        'download_pdf',
        'download_png',
        'one_per_submission',
        'dcr_record_status_id',
        'dcr_record_url_field_id',
        'dcr_record_allow_unfinished',
        'dcr_record_visibility_id',
        'dcr_record_editability_id',
        'dcr_record_visibility_def',
        'dcr_record_editability_def',
        'dcr_record_save_visibility_def',
        'dcr_record_save_editability_def',
        'stored_row_protection',
        'stored_row_pass_id',

        //submission
        'dcr_active_notif',
        'dcr_confirm_msg',
        'dcr_unique_msg',
        'dcr_email_field_id',
        'dcr_cc_email_field_id',
        'dcr_bcc_email_field_id',
        'dcr_email_field_static',
        'dcr_cc_email_field_static',
        'dcr_bcc_email_field_static',
        'dcr_email_subject',
        'dcr_addressee_field_id',
        'dcr_addressee_txt',
        'dcr_signature_txt',
        'dcr_email_message',
        'dcr_email_format',
        'dcr_email_col_group_id',
        //save
        'dcr_save_active_notif',
        'dcr_save_confirm_msg',
        'dcr_save_unique_msg',
        'dcr_save_email_field_id',
        'dcr_save_cc_email_field_id',
        'dcr_save_bcc_email_field_id',
        'dcr_save_email_field_static',
        'dcr_save_cc_email_field_static',
        'dcr_save_bcc_email_field_static',
        'dcr_save_email_subject',
        'dcr_save_addressee_field_id',
        'dcr_save_addressee_txt',
        'dcr_save_signature_txt',
        'dcr_save_email_message',
        'dcr_save_email_format',
        'dcr_save_email_col_group_id',
        //update
        'dcr_upd_active_notif',
        'dcr_upd_confirm_msg',
        'dcr_upd_unique_msg',
        'dcr_upd_email_field_id',
        'dcr_upd_cc_email_field_id',
        'dcr_upd_bcc_email_field_id',
        'dcr_upd_email_field_static',
        'dcr_upd_cc_email_field_static',
        'dcr_upd_bcc_email_field_static',
        'dcr_upd_email_subject',
        'dcr_upd_addressee_field_id',
        'dcr_upd_addressee_txt',
        'dcr_upd_signature_txt',
        'dcr_upd_email_message',
        'dcr_upd_email_format',
        'dcr_upd_email_col_group_id',
    ];

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->_table->_user;
    }

    /**
     * @var array
     */
    public $design_tab = [
        'dcr_sec_background_by',
        'dcr_sec_scroll_style',
        'dcr_sec_slide_top_header',
        'dcr_sec_slide_progresbar',
        'dcr_sec_line_top',
        'dcr_sec_line_bot',
        'dcr_sec_line_color',
        'dcr_sec_line_thick',
        'dcr_sec_bg_top',
        'dcr_sec_bg_bot',
        'dcr_sec_bg_img',
        'dcr_sec_bg_img_fit',
        'dcr_form_line_height',
        'dcr_form_font_size',
        'dcr_flow_header_stick',

        'dcr_title',
        'dcr_title_width',
        'dcr_title_height',
        'dcr_title_bg_color',
        'dcr_title_font_type',
        'dcr_title_font_size',
        'dcr_title_font_color',
        'dcr_title_font_style',
        'dcr_title_bg_img',
        'dcr_title_bg_fit',
        'dcr_title_background_by',

        'dcr_form_line_type',// ['line','space']
        'dcr_form_line_top',
        'dcr_form_line_bot',
        'dcr_form_line_thick',
        'dcr_form_line_radius',
        'dcr_form_line_color',
        'dcr_form_bg_color',
        'dcr_form_transparency',
        'dcr_form_message',
        'dcr_form_message_font',
        'dcr_form_message_size',
        'dcr_form_message_color',
        'dcr_form_message_style',
        'dcr_form_width',
        'dcr_form_shadow',
        'dcr_form_shadow_color',
        'dcr_form_shadow_dir',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_data_request_rights', 'table_data_request_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_data_request_id', 'table_permission_id', 'can_edit']);
    }
    public function _dcr_rights() {
        return $this->hasMany(TableDataRequestRight::class, 'table_data_request_id', 'id');
    }

    public function _user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //----- table column groups
    public function _column_groups()
    {
        return $this->belongsToMany(TableColumnGroup::class, 'table_data_requests_2_table_column_groups', 'table_data_requests_id', 'table_column_group_id')
            ->as('_link')
            ->withPivot(['view', 'edit']);
    }

    public function _data_request_columns()
    {
        return $this->hasMany(TableDataRequestColumn::class, 'table_data_requests_id', 'id');
    }

    //--------------------------

    public function _default_fields()
    {
        return $this->hasMany(TableDataRequestDefaultField::class, 'table_data_requests_id', 'id');
    }

    public function _dcr_linked_tables()
    {
        return $this->hasMany(DcrLinkedTable::class, 'table_request_id', 'id');
    }

    public function _fields_pivot()
    {
        return $this->hasMany(TableDataRequest2Fields::class, 'table_data_requests_id', 'id');
    }

    public function _dcr_pass_field()
    {
        return $this->hasOne(TableField::class, 'id', 'stored_row_pass_id');
    }

    //submission
    public function _dcr_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_email_field_id');
    }

    public function _dcr_cc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_cc_email_field_id');
    }

    public function _dcr_bcc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_bcc_email_field_id');
    }

    public function _dcr_addressee_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_addressee_field_id');
    }

    public function _dcr_email_col_group()
    {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_email_col_group_id');
    }

    //save
    public function _dcr_save_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_email_field_id');
    }

    public function _dcr_save_cc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_cc_email_field_id');
    }

    public function _dcr_save_bcc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_bcc_email_field_id');
    }

    public function _dcr_save_addressee_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_addressee_field_id');
    }

    public function _dcr_save_email_col_group()
    {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_save_email_col_group_id');
    }

    //update
    public function _dcr_upd_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_email_field_id');
    }

    public function _dcr_upd_cc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_cc_email_field_id');
    }

    public function _dcr_upd_bcc_email_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_bcc_email_field_id');
    }

    public function _dcr_upd_addressee_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_addressee_field_id');
    }

    public function _dcr_upd_email_col_group()
    {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_upd_email_col_group_id');
    }

    public function _dcr_record_url_field()
    {
        return $this->hasOne(TableField::class, 'id', 'dcr_record_url_field_id');
    }

    public function _dcr_linked_notifs() {//'prefix'+linked_notifs
        return $this->hasMany(DcrNotifLinkedTable::class, 'dcr_id', 'id')
            ->where('type', 'submit');
    }
    public function _dcr_upd_linked_notifs() {//'prefix'+linked_notifs
        return $this->hasMany(DcrNotifLinkedTable::class, 'dcr_id', 'id')
            ->where('type', 'update');
    }
    public function _dcr_save_linked_notifs() {//'prefix'+linked_notifs
        return $this->hasMany(DcrNotifLinkedTable::class, 'dcr_id', 'id')
            ->where('type', 'save');
    }
}
