<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\AppTheme;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRcmapPosition;
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
 * @property string $initial_name
 * @property int $user_id
 * @property int $rows_per_page
 * @property int $row_space_size
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
 * @property int|null $add_twilio
 * @property int|null $add_tournament
 * @property int|null $add_simplemap
 * @property int|null $add_report
 * @property int|null $add_ai
 * @property int|null $add_grouping
 * @property int|null $num_rows
 * @property int|null $num_columns
 * @property int|null $num_collaborators
 * @property int|null $pub_hidden
 * @property int|null $filters_on_top
 * @property int|null $openai_tb_key_id
 * @property string $filters_ontop_pos
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
 * @property int $max_mirrors_in_one_row
 * @property int $search_results_len
 * @property string $table_engine
 * @property int $auto_enable_virtual_scroll
 * @property int $auto_enable_virtual_scroll_when
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
 * @property int $mirror_edited_underline
 * @property int $no_span_data_cell_in_popup
 * @property int $refill_auto_oncopy
 * @property int $unit_conv_by_user
 * @property int $unit_conv_by_system
 * @property int $unit_conv_by_lib
 * @property int|null $unit_conv_formula_fld_id
 * @property int|null $unit_conv_formula_reverse_fld_id
 * @property int $board_title_width
 * @property int $board_image_width
 * @property int $board_image_height
 * @property int|null $board_image_fld_id
 * @property string|null $board_display_position
 * @property string $board_display_view
 * @property string $board_display_fit
 * @property int|null $vert_tb_bgcolor
 * @property int $vert_tb_rowspacing
 * @property int $vert_tb_hdrwidth
 * @property int $vert_tb_width_px
 * @property int $vert_tb_width_px_min
 * @property int|null $vert_tb_width_px_max
 * @property int $vert_tb_height
 * @property int $vert_tb_height_min
 * @property int|null $vert_tb_height_max
 * @property int $linkd_tb_width_px
 * @property int $linkd_tb_width_px_min
 * @property int|null $linkd_tb_width_px_max
 * @property int $linkd_tb_height
 * @property int $linkd_tb_height_min
 * @property int|null $linkd_tb_height_max
 * @property int|null $linkd_tb_floating
 * @property int|null $account_api_key_id
 * @property string|null $import_web_scrap_save
 * @property string|null $import_gsheet_save
 * @property string|null $import_airtable_save
 * @property string|null $import_transpose_save
 * @property string|null $import_jira_save
 * @property string|null $import_salesforce_save
 * @property string|null $import_csv_save
 * @property string|null $import_mysql_save
 * @property string|null $import_paste_save
 * @property string|null $import_table_ocr_save
 * @property string|null $import_last_cols_corresp
 * @property string|null $import_last_jira_action
 * @property string|null $import_last_salesforce_action
 * @property string $primary_view
 * @property int $primary_width
 * @property string $primary_align
 * @property int|null $listing_fld_id
 * @property int|null $listing_rowswi
 * @property int $single_view_active
 * @property string $single_view_regenerate_datarange
 * @property int|null $single_view_permission_id
 * @property int|null $single_view_status_id
 * @property int|null $single_view_edit_id
 * @property int|null $single_view_url_id
 * @property int|null $single_view_regenerate
 * @property int|null $single_view_password_id
 * @property string $single_view_background_by
 * @property string|null $single_view_bg_color
 * @property string|null $single_view_bg_img
 * @property string|null $single_view_bg_fit
 * @property int $single_view_form_width
 * @property string|null $single_view_form_background
 * @property string $single_view_form_align_h
 * @property string $single_view_form_align_v
 * @property int $single_view_form_transparency
 * @property int $single_view_form_line_height
 * @property int $single_view_form_font_size
 * @property string|null $single_view_hedaer
 * @property string|null $single_view_header_font
 * @property int $single_view_header_font_size
 * @property string|null $single_view_header_color
 * @property string|null $single_view_header_background
 * @property string $single_view_header_align_h
 * @property string $single_view_header_align_v
 * @property int $single_view_header_height
 * @property int|null $multi_link_app_fld_id
 * @property string|null $attachments_size
 * @property Collection|TableAlert[] $_alerts
 * @property Collection|TableKanbanSettings[] $_kanban_settings
 * @property Collection|TableAi[] $_table_ais
 * @property Collection|TableGantt[] $_gantt_addons
 * @property Collection|TableTournament[] $_tournaments
 * @property Collection|TableSimplemap[] $_simplemaps
 * @property Collection|TableGrouping[] $_groupings
 * @property Collection|TableReport[] $_reports
 * @property Collection|TableReportTemplate[] $_report_templates
 * @property Collection|File[] $_attached_files
 * @property Collection|TableBackup[] $_backups
 * @property Collection|TableChart[] $_bi_charts
 * @property Collection|TableChartTab[] $_chart_tabs
 * @property Collection|TableColumnGroup[] $_column_groups
 * @property Collection|Communication[] $_communications
 * @property Collection|CondFormat[] $_cond_formats
 * @property UserConnection|null $_connection
 * @property User|null $_created_user
 * @property TableUserSetting|null $_cur_settings
 * @property TableStatuse|null $_cur_statuse
 * @property Collection|DDL[] $_ddls
 * @property Collection|TableEmailAddonSetting[] $_email_addon_settings
 * @property Collection|TableCalendar[] $_calendar_addons
 * @property Collection|TableTwilioAddonSetting[] $_twilio_addon_settings
 * @property Collection|TableField[] $_fields
 * @property Collection|TableField[] $_fields_are_attachments
 * @property Collection|TableField[] $_fields_are_formula
 * @property Collection|Folder2Table[] $_folder_links
 * @property Collection|Folder[] $_folders
 * @property TableView|null $_initial_view
 * @property Folder2Table|null $_link_initial_folder
 * @property User|null $_modified_user
 * @property Collection|Folder2Table[] $_public_links
 * @property Collection|TableRefCondition[] $_ref_conditions
 * @property Collection|TableRcmapPosition[] $_rcmap_positions
 * @property Collection|TableRowGroup[] $_row_groups
 * @property Collection|TableSharedName[] $_shared_names
 * @property Collection|TablePermission[] $_table_permissions
 * @property Collection|TableReference[] $_table_references
 * @property Collection|TableDataRequest[] $_table_requests
 * @property AppTheme|null $_theme
 * @property Table|null $_uc_table
 * @property Collection|TableField[] $_unique_fields
 * @property User $_user
 * @property Collection|UserGroup[] $_user_groups
 * @property TableNote|null $_user_notes
 * @property Collection|TableView[] $_views
 * @property TableColumnGroup|null $_visitor_column_group
 * @property Collection|TableSavedFilter[] $_saved_filters
 * @property TableField|null $_srv_url
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
        'initial_name',
        'user_id',
        'rows_per_page',
        'row_space_size',
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
        'filters_on_top',
        'filters_ontop_pos',
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
        'board_title_width',
        'board_image_width',
        'board_image_height',
        'board_image_fld_id',
        'board_display_position',
        'board_display_view',
        'board_display_fit',
        'max_rows_in_link_popup',
        'max_mirrors_in_one_row',
        'search_results_len',
        'table_engine',
        'auto_enable_virtual_scroll',
        'auto_enable_virtual_scroll_when',
        'max_filter_elements',
        'google_api_key',
        'api_key_mode',
        'account_api_key_id',
        'edit_one_click',
        'vert_tb_bgcolor',
        'vert_tb_hdrwidth',
        'vert_tb_floating',
        'vert_tb_rowspacing',
        'vert_tb_width_px',
        'vert_tb_width_px_min',
        'vert_tb_width_px_max',
        'vert_tb_height',
        'vert_tb_height_min',
        'vert_tb_height_max',
        'linkd_tb_width_px',
        'linkd_tb_width_px_min',
        'linkd_tb_width_px_max',
        'linkd_tb_height',
        'linkd_tb_height_min',
        'linkd_tb_height_max',
        'primary_view',
        'primary_width',
        'primary_align',
        'listing_fld_id',
        'listing_rowswi',
        'mirror_edited_underline',
        'no_span_data_cell_in_popup',
        'refill_auto_oncopy',
        'openai_tb_key_id',
        'multi_link_app_fld_id',

        'single_view_active',
        'single_view_regenerate_datarange',
        'single_view_permission_id',
        'single_view_status_id',
        'single_view_edit_id',
        'single_view_url_id',
        'single_view_regenerate',
        'single_view_password_id',
        'single_view_background_by',//['color', 'image']
        'single_view_bg_color',
        'single_view_bg_img',
        'single_view_bg_fit',
        'single_view_form_width',
        'single_view_form_background',
        'single_view_form_align_h',
        'single_view_form_align_v',
        'single_view_form_transparency',
        'single_view_form_line_height',
        'single_view_form_font_size',
        'single_view_header',
        'single_view_header_font',
        'single_view_header_font_size',
        'single_view_header_color',
        'single_view_header_background',
        'single_view_header_align_h',
        'single_view_header_align_v',
        'single_view_header_height',

        'add_map',
        'add_bi',
        'add_request',
        'add_alert',
        'add_kanban',
        'add_gantt',
        'add_email',
        'add_calendar',
        'add_twilio',
        'add_tournament',
        'add_simplemap',
        'add_grouping',
        'add_report',
        'add_ai',

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

        'import_web_scrap_save',
        'import_gsheet_save',
        'import_airtable_save',
        'import_transpose_save',
        'import_jira_save',
        'import_salesforce_save',
        'import_csv_save',
        'import_mysql_save',
        'import_paste_save',
        'import_table_ocr_save',
        'import_last_cols_corresp',
        'import_last_jira_action',//Stored in user's timezone as Jira has some auto-conversions
        'import_last_salesforce_action',//Stored in user's timezone

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
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
        return $this->hasMany(CondFormat::class, 'table_id', 'id')
            ->orderBy('row_order', 'desc');
    }

    public function _ref_conditions()
    {
        return $this->hasMany(TableRefCondition::class, 'table_id', 'id');
    }

    public function _rcmap_positions()
    {
        return $this->hasMany(TableRcmapPosition::class, 'table_id', 'id');
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

    public function _tournaments()
    {
        return $this->hasMany(TableTournament::class, 'table_id', 'id');
    }

    public function _simplemaps()
    {
        return $this->hasMany(TableSimplemap::class, 'table_id', 'id');
    }

    public function _groupings()
    {
        return $this->hasMany(TableGrouping::class, 'table_id', 'id');
    }

    public function _reports()
    {
        return $this->hasMany(TableReport::class, 'table_id', 'id');
    }

    public function _report_templates()
    {
        return $this->hasMany(TableReportTemplate::class, 'table_id', 'id');
    }

    public function _bi_charts()
    {
        return $this->hasMany(TableChart::class, 'table_id', 'id');
    }

    public function _chart_tabs()
    {
        return $this->hasMany(TableChartTab::class, 'table_id', 'id');
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

    public function _twilio_addon_settings()
    {
        return $this->hasMany(TableTwilioAddonSetting::class, 'table_id', 'id');
    }

    public function _gantt_addons()
    {
        return $this->hasMany(TableGantt::class, 'table_id', 'id');
    }

    public function _map_addons()
    {
        return $this->hasMany(TableMap::class, 'table_id', 'id');
    }

    public function _calendar_addons()
    {
        return $this->hasMany(TableCalendar::class, 'table_id', 'id');
    }

    public function _kanban_settings()
    {
        return $this->hasMany(TableKanbanSettings::class, 'table_id', 'id');
    }

    public function _table_ais()
    {
        return $this->hasMany(TableAi::class, 'table_id', 'id');
    }

    public function _saved_filters()
    {
        return $this->hasMany(TableSavedFilter::class, 'table_id', 'id');
    }

    public function _fields_pivot() //SRV
    {
        return $this->hasMany(TableSRV2Fields::class, 'table_id', 'id');
    }

    public function _srv_url() //SRV
    {
        return $this->hasOne(TableField::class, 'id', 'single_view_url_id');
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


    /**
     * @return void
     */
    public function activateVirtualScrollIfNeeded()
    {
        if (
            $this->table_engine == 'default'
            && $this->auto_enable_virtual_scroll
            && ($this->rows_per_page * $this->_fields()->count()) > $this->auto_enable_virtual_scroll_when
        ) {
            Table::where('id', $this->id)->update(['table_engine' => 'virtual']);
        }
    }
}
