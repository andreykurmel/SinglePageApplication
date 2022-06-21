<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\AppTheme;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\DDL;
use Vanguard\Models\File;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\Models\User\Communication;
use Vanguard\Models\User\UserConnection;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\Table
 *
 * @property int $id
 * @property int $is_system
 * @property string $db_name
 * @property string $name
 * @property int $user_id
 * @property int $rows_per_page
 * @property string|null $notes
 * @property string|null $add_notes
 * @property string $source
 * @property int|null $connection_id
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int|null $add_map
 * @property int|null $add_bi
 * @property int|null $add_alert
 * @property int|null $add_kanban
 * @property int|null $add_gantt
 * @property int|null $add_email
 * @property int|null $add_calendar
 * @property int|null $num_rows
 * @property int|null $num_columns
 * @property int|null $num_collaborators
 * @property int|null $pub_hidden
 * @property int|null $map_icon_field_id
 * @property string|null $map_icon_style
 * @property int|null $add_request
 * @property string|null $hash
 * @property float $avg_row_length
 * @property int|null $initial_view_id
 * @property string $usage_type
 * @property string|null $add_notes_2
 * @property string|null $version_hash
 * @property int $enabled_activities
 * @property int|null $autoload_new_data
 * @property int|null $app_theme_id
 * @property int $menutree_order
 * @property int|null $rows_limited_for_user
 * @property string|null $row_hash
 * @property int $board_view_height
 * @property int $max_rows_in_link_popup
 * @property int $search_results_len
 * @property int|null $address_fld__source_id
 * @property int|null $address_fld__street_address
 * @property int|null $address_fld__street
 * @property int|null $address_fld__city
 * @property int|null $address_fld__state
 * @property int|null $address_fld__zipcode
 * @property int|null $address_fld__country
 * @property int|null $address_fld__lat
 * @property int|null $address_fld__long
 * @property int $is_public
 * @property int $max_filter_elements
 * @property string|null $google_api_key
 * @property string $api_key_mode
 * @property int $unit_conv_is_active
 * @property int|null $unit_conv_table_id
 * @property int|null $unit_conv_from_fld_id
 * @property int|null $unit_conv_to_fld_id
 * @property int|null $unit_conv_operator_fld_id
 * @property int|null $unit_conv_factor_fld_id
 * @property int|null $address_fld__countyarea
 * @property int $edit_one_click
 * @property int $unit_conv_by_user
 * @property int $unit_conv_by_system
 * @property int $unit_conv_by_lib
 * @property int|null $unit_conv_formula_fld_id
 * @property int|null $unit_conv_formula_reverse_fld_id
 * @property int|null $map_multiinfo
 * @property int $board_image_width
 * @property int $kanban_form_table
 * @property int|null $kanban_center_align
 * @property int $kanban_card_width
 * @property int|null $kanban_card_height
 * @property string $kanban_header_color
 * @property string|null $kanban_sort_type
 * @property int|null $kanban_picture_field
 * @property int"null $kanban_picture_width
 * @property int|null $gantt_navigation
 * @property string|null $gantt_info_type
 * @property int|null $gantt_show_names
 * @property int|null $gantt_highlight
 * @property int $kanban_hide_empty_tab
 * @property string|null $gantt_day_format
 * @property int $bi_fix_layout
 * @property int $bi_can_add
 * @property int $bi_cell_height
 * @property int $bi_can_settings
 * @property int $bi_cell_spacing
 * @property int|null $vert_tb_bgcolor
 * @property int $vert_tb_hdrwidth
 * @property int|null $vert_tb_floating
 * @property int|null $account_api_key_id
 * @property string|null $calendar_locale
 * @property string|null $calendar_timezone
 * @property string|null $import_web_scrap_save
 * @property string|null $import_gsheet_save
 * @property string|null $import_airtable_save
 * @property string|null $import_csv_save
 * @property string|null $import_mysql_save
 * @property string|null $import_paste_save
 * @property string|null $import_table_ocr_save
 * @property int|null $map_position_refid
 * @property int|null $map_popup_hdr_id
 * @property int $single_view_active
 * @property int|null $single_view_permission_id
 * @property int|null $single_view_status_id
 * @property int|null $single_view_password_id
 * @property string $single_view_background_by
 * @property string|null $single_view_bg_color
 * @property string|null $single_view_bg_img
 * @property string|null $single_view_bg_fit
 * @property int $single_view_form_width
 * @property string|null $single_view_form_color
 * @property int $single_view_form_transparency
 * @property int $single_view_form_line_height
 * @property int $single_view_form_font_size
 * @property string|null $attachments_size
 * @property TableAlert[] $_alerts
 * @property int|null $_alerts_count
 * @property File[] $_attached_files
 * @property int|null $_attached_files_count
 * @property TableBackup[] $_backups
 * @property int|null $_backups_count
 * @property TableChart[] $_charts
 * @property int|null $_charts_count
 * @property TableColumnGroup[] $_column_groups
 * @property int|null $_column_groups_count
 * @property Communication[] $_communications
 * @property int|null $_communications_count
 * @property CondFormat[] $_cond_formats
 * @property int|null $_cond_formats_count
 * @property UserConnection|null $_connection
 * @property User|null $_created_user
 * @property TableUserSetting|null $_cur_settings
 * @property TableStatuse|null $_cur_statuse
 * @property DDL[] $_ddls
 * @property int|null $_ddls_count
 * @property TableEmailAddonSetting|null $_email_addon_settings
 * @property TableField[] $_fields
 * @property int|null $_fields_count
 * @property TableField[] $_fields_are_attachments
 * @property int|null $_fields_are_attachments_count
 * @property TableField[] $_fields_are_formula
 * @property int|null $_fields_are_formula_count
 * @property Folder2Table[] $_folder_links
 * @property int|null $_folder_links_count
 * @property Folder[] $_folders
 * @property int|null $_folders_count
 * @property TableView|null $_initial_view
 * @property Folder2Table|null $_link_initial_folder
 * @property User|null $_modified_user
 * @property Folder2Table[] $_public_links
 * @property int|null $_public_links_count
 * @property TableRefCondition[] $_ref_conditions
 * @property int|null $_ref_conditions_count
 * @property TableRowGroup[] $_row_groups
 * @property int|null $_row_groups_count
 * @property TableSharedName[] $_shared_names
 * @property int|null $_shared_names_count
 * @property TablePermission[] $_table_permissions
 * @property int|null $_table_permissions_count
 * @property TableReference[] $_table_references
 * @property int|null $_table_references_count
 * @property TableDataRequest[] $_table_requests
 * @property int|null $_table_requests_count
 * @property AppTheme|null $_theme
 * @property Table|null $_uc_table
 * @property TableField[] $_unique_fields
 * @property int|null $_unique_fields_count
 * @property User $_user
 * @property UserGroup[] $_user_groups
 * @property int|null $_user_groups_count
 * @property TableNote|null $_user_notes
 * @property TableView[] $_views
 * @property int|null $_views_count
 * @property TableColumnGroup|null $_visitor_column_group
 * @mixin Eloquent
 */
class Table extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'tables';

    /**
     * @var string[]
     */
    protected $fillable = [
        'is_system', //0 - regular, 1 - system in 'app_sys', 2 - system in 'app_correspondence'
        'db_name',
        'name',
        'user_id',
        'rows_per_page',
        'notes',
        'add_notes',
        'add_notes_2',
        'source',
        'connection_id',
        'num_rows',
        'num_columns',
        'num_collaborators',
        'avg_row_length',
        'pub_hidden',
        'is_public',
        'hash',
        'version_hash',
        'autoload_new_data',
        'enabled_activities',
        'initial_view_id',
        'usage_type',
        'attachments_size',
        'menutree_order',
        'only_own_headers',
        'board_view_height',
        'board_image_width',
        'max_rows_in_link_popup',
        'search_results_len',
        'max_filter_elements',
        'google_api_key',
        'api_key_mode',
        'account_api_key_id',
        'edit_one_click',
        'vert_tb_bgcolor',
        'vert_tb_hdrwidth',
        'vert_tb_floating',
        'map_multiinfo',
        'map_icon_field_id',
        'map_icon_style',
        'map_position_refid',//RefCondition
        'map_popup_hdr_id',//RefCondition

        'single_view_active',
        'single_view_permission_id',
        'single_view_status_id',
        'single_view_password_id',
        'single_view_background_by',//['color', 'image']
        'single_view_bg_color',
        'single_view_bg_img',
        'single_view_bg_fit',
        'single_view_form_width',
        'single_view_form_color',
        'single_view_form_transparency',
        'single_view_form_line_height',
        'single_view_form_font_size',

        'add_map',
        'add_bi',
        'add_request',
        'add_alert',
        'add_kanban',
        'add_gantt',
        'add_email',
        'add_calendar',

        'bi_fix_layout',
        'bi_can_add',
        'bi_can_settings',
        'bi_cell_height',
        'bi_cell_spacing',

        'address_fld__source_id',
        'address_fld__street_address',
        'address_fld__street',
        'address_fld__city',
        'address_fld__state',
        'address_fld__zipcode',
        'address_fld__countyarea',
        'address_fld__country',
        'address_fld__lat',
        'address_fld__long',
        'map_popup_header_color',
        'map_popup_width',
        'map_popup_height',
        'map_picture_style',
        'map_picture_field',
        'map_picture_width',

        'unit_conv_is_active',
        'unit_conv_by_user',
        'unit_conv_table_id',
        'unit_conv_from_fld_id',
        'unit_conv_to_fld_id',
        'unit_conv_operator_fld_id',
        'unit_conv_factor_fld_id',
        'unit_conv_formula_fld_id',
        'unit_conv_formula_reverse_fld_id',
        'unit_conv_by_system',
        'unit_conv_by_lib',

        'kanban_form_table',
        'kanban_center_align',
        'kanban_card_width',
        'kanban_card_height',
        'kanban_sort_type',
        'kanban_header_color',
        'kanban_hide_empty_tab',
        'kanban_picture_field',
        'kanban_picture_width',

        'gantt_info_type',
        'gantt_navigation',
        'gantt_show_names',
        'gantt_highlight',
        'gantt_day_format',

        'calendar_locale',
        'calendar_timezone',

        'import_web_scrap_save',
        'import_gsheet_save',
        'import_airtable_save',
        'import_csv_save',
        'import_mysql_save',
        'import_paste_save',
        'import_table_ocr_save',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    /**
     * @var string[]
     */
    protected $for_copy = [
        'rows_per_page',
        'notes',
        'add_notes',
        'add_notes_2',
        'source',
        'num_rows',
        'num_columns',
        'num_collaborators',
        'avg_row_length',
        'pub_hidden',
        'is_public',
        'autoload_new_data',
        'enabled_activities',
        'usage_type',
        'attachments_size',
        'menutree_order',
        'only_own_headers',
        'board_view_height',
        'board_image_width',
        'max_rows_in_link_popup',
        'search_results_len',
        'max_filter_elements',
        'google_api_key',
        'api_key_mode',
        'edit_one_click',
        'vert_tb_bgcolor',
        'vert_tb_hdrwidth',
        'vert_tb_floating',

        'add_map',
        'add_bi',
        'add_request',
        'add_alert',
        'add_kanban',
        'add_gantt',
        'add_email',
        'add_calendar',
    ];

    /**
     *  #model_hook
     */
    public static function boot()
    {
        parent::boot();
        /*Table::updating(function ($a) {
            dd('updating', $a);
            return false;
        });*/
    }

    /**
     * @return array
     */
    public function getCopyAttrs()
    {
        return collect($this->getAttributes())
            ->only($this->for_copy)
            ->toArray();
    }


    public function _user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _folders()
    {
        return $this
            ->belongsToMany(Folder::class, 'folders_2_tables', 'table_id', 'folder_id')
            ->as('link')
            ->withPivot(['id', 'user_id', 'type', 'structure', 'is_folder_link']);
    }

    public function _link_initial_folder()
    {
        return $this->hasOne(Folder2Table::class, 'table_id', 'id')
            ->where('folders_2_tables.structure', 'private')
            ->where('folders_2_tables.type', 'table');
    }

    public function _folder_links()
    {
        return $this->hasMany(Folder2Table::class, 'table_id', 'id');
    }

    public function _public_links()
    {
        return $this->hasMany(Folder2Table::class, 'table_id', 'id')
            ->where('structure', '=', 'public');
    }

    public function _table_permissions()
    {
        return $this->hasMany(TablePermission::class, 'table_id', 'id')
            ->orderBy('table_permissions.is_system', 'desc');
    }

    public function _table_requests()
    {
        return $this->hasMany(TableDataRequest::class, 'table_id', 'id');
    }

    // Data Set Permissions
    public function _user_groups()
    {
        return $this->morphToMany(
            UserGroup::class,
            'object',
            'user_groups_3_tables_and_folders'
        );
    }

    public function _row_groups()
    {
        return $this->hasMany(TableRowGroup::class, 'table_id', 'id');
    }

    public function _column_groups()
    {
        return $this->hasMany(TableColumnGroup::class, 'table_id', 'id');
    }

    public function _visitor_column_group()
    {
        return $this->hasOne(TableColumnGroup::class, 'table_id', 'id')
            ->where('is_system', '=', 1);
    }

    public function _cond_formats()
    {
        return $this->hasMany(CondFormat::class, 'table_id', 'id');
    }

    public function _ref_conditions()
    {
        return $this->hasMany(TableRefCondition::class, 'table_id', 'id');
    }

    //------------

    public function _fields()
    {
        return $this->hasMany(TableField::class, 'table_id', 'id');
    }

    public function _fields_are_formula()
    {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.input_type', 'Formula');
    }

    public function _fields_are_attachments()
    {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.f_type', 'Attachment');
    }

    public function _unique_fields()
    {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.is_unique_collection', 1);
    }

    public function _ddls()
    {
        return $this->hasMany(DDL::class, 'table_id', 'id');
    }

    public function _table_references()
    {
        return $this->hasMany(TableReference::class, 'table_id', 'id');
    }

    public function _views()
    {
        return $this->hasMany(TableView::class, 'table_id', 'id');
    }

    public function _initial_view()
    {
        return $this->hasOne(TableView::class, 'id', 'initial_view_id');
    }

    public function _user_notes()
    {
        return $this->hasOne(TableNote::class, 'table_id', 'id')
            ->where('user_id', '=', auth()->id());
    }

    public function _connection()
    {
        return $this->hasOne(UserConnection::class, 'id', 'connection_id')
            ->where('user_id', '=', auth()->id());
    }

    public function _attached_files()
    {
        return $this->hasMany(File::class, 'table_id', 'id')
            ->where('table_field_id', '=', 0)
            ->where('row_id', '=', 0);
    }

    public function _communications()
    {
        return $this->hasMany(Communication::class, 'table_id', 'id')
            ->orderBy('date', 'desc');
    }

    public function _backups()
    {
        return $this->hasMany(TableBackup::class, 'table_id', 'id');
    }

    public function _alerts()
    {
        return $this->hasMany(TableAlert::class, 'table_id', 'id');
    }

    public function _charts()
    {
        return $this->hasMany(TableChart::class, 'table_id', 'id');
    }

    public function _shared_names()
    {
        return $this->hasMany(TableSharedName::class, 'table_id', 'id');
    }


    public function _cur_statuse()
    {
        return $this->hasOne(TableStatuse::class, 'table_id', 'id')
            ->where('user_id', auth()->id());
    }

    public function _cur_settings()
    {
        return $this->hasOne(TableUserSetting::class, 'table_id', 'id')
            ->where('user_id', auth()->id());
    }

    public function _owner_settings()
    {
        if (!$this->getAttribute('user_id')) {
            throw new Exception('Table::_owner_settings must not be called in eager loading.');
        }

        return $this->hasOne(TableUserSetting::class, 'table_id', 'id')
            ->where('user_id', $this->getAttribute('user_id'));
    }

    public function _theme()
    {
        return $this->hasOne(AppTheme::class, 'obj_id', 'id')
            ->where('obj_type', 'table');
    }

    public function _email_addon_settings()
    {
        return $this->hasMany(TableEmailAddonSetting::class, 'table_id', 'id');
    }


    /* Unit Conversions */
    public function _uc_table()
    {
        return $this->hasOne(Table::class, 'id', 'unit_conv_table_id');
    }
    /* Unit Conversions */

    public function _created_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
