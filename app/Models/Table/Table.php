<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\AppTheme;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\DDL;
use Vanguard\Models\File;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\Models\User\Communication;
use Vanguard\Models\User\UserConnection;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

class Table extends Model
{
    protected $table = 'tables';

    public $timestamps = false;

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
        'map_multiinfo',
        'num_rows',
        'num_columns',
        'num_collaborators',
        'avg_row_length',
        'pub_hidden',
        'is_public',
        'map_icon_field_id',
        'map_icon_style',
        'hash',
        'version_hash',
        'autoload_new_data',
        'initial_view_id',
        'usage_type',
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
        'kanban_card_width',
        'kanban_card_height',
        'kanban_sort_type',
        'kanban_header_color',
        'kanban_hide_empty_tab',
        'kanban_picture_style',//scroll,slide

        'gantt_info_type',
        'gantt_navigation',
        'gantt_show_names',
        'gantt_highlight',
        'gantt_day_format',

        'calendar_locale',
        'calendar_timezone',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    protected $not_copy = [
        'db_name',
        'name',
        'user_id',
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
    public function getCopyAttrs() {
        return collect( $this->getAttributes() )
            ->except( $this->not_copy )
            ->toArray();
    }


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _folders() {
        return $this
            ->belongsToMany(Folder::class, 'folders_2_tables', 'table_id', 'folder_id')
            ->as('link')
            ->withPivot(['id', 'user_id', 'type', 'structure']);
    }

    public function _link_initial_folder() {
        return $this->hasOne(Folder2Table::class, 'table_id', 'id')
            ->where('folders_2_tables.structure', 'private')
            ->where('folders_2_tables.type', 'table');
    }

    public function _folder_links() {
        return $this->hasMany(Folder2Table::class, 'table_id', 'id');
    }

    public function _public_links() {
        return $this->hasMany(Folder2Table::class, 'table_id', 'id')
            ->where('structure', '=', 'public');
    }

    public function _table_permissions() {
        return $this->hasMany(TablePermission::class, 'table_id', 'id')
            ->orderBy('table_permissions.is_system', 'desc');
    }

    // Data Set Permissions
    public function _user_groups() {
        return $this->morphToMany(
            UserGroup::class,
            'object',
            'user_groups_3_tables_and_folders'
        );
    }

    public function _row_groups() {
        return $this->hasMany(TableRowGroup::class, 'table_id', 'id');
    }

    public function _column_groups() {
        return $this->hasMany(TableColumnGroup::class, 'table_id', 'id');
    }
    public function _visitor_column_group() {
        return $this->hasOne(TableColumnGroup::class, 'table_id', 'id')
            ->where('is_system', '=', 1);
    }

    public function _cond_formats() {
        return $this->hasMany(CondFormat::class, 'table_id', 'id');
    }

    public function _ref_conditions() {
        return $this->hasMany(TableRefCondition::class, 'table_id', 'id');
    }
    //------------

    public function _fields() {
        return $this->hasMany(TableField::class, 'table_id', 'id');
    }
    public function _fields_are_formula() {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.input_type', 'Formula');
    }
    public function _fields_are_attachments() {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.f_type', 'Attachment');
    }
    public function _unique_fields() {
        return $this->hasMany(TableField::class, 'table_id', 'id')
            ->where('table_fields.is_unique_collection', 1);
    }

    public function _ddls() {
        return $this->hasMany(DDL::class, 'table_id', 'id');
    }

    public function _table_references() {
        return $this->hasMany(TableReference::class, 'table_id', 'id');
    }

    public function _views() {
        return $this->hasMany(TableView::class, 'table_id', 'id');
    }

    public function _initial_view() {
        return $this->hasOne(TableView::class, 'id', 'initial_view_id');
    }

    public function _user_notes() {
        return $this->hasOne(TableNote::class, 'table_id', 'id')
            ->where('user_id', '=', auth()->id());
    }

    public function _connection() {
        return $this->hasOne(UserConnection::class, 'id', 'connection_id')
            ->where('user_id', '=', auth()->id());
    }

    public function _attached_files() {
        return $this->hasMany(File::class, 'table_id', 'id')
            ->where('table_field_id', '=', 0)
            ->where('row_id', '=', 0);
    }

    public function _communications() {
        return $this->hasMany(Communication::class, 'table_id', 'id')
            ->orderBy('date', 'desc');
    }

    public function _backups() {
        return $this->hasMany(TableBackup::class, 'table_id', 'id');
    }

    public function _alerts() {
        return $this->hasMany(TableAlert::class, 'table_id', 'id');
    }

    public function _charts() {
        return $this->hasMany(TableChart::class, 'table_id', 'id');
    }

    public function _shared_names() {
        return $this->hasMany(TableSharedName::class, 'table_id', 'id');
    }


    public function _cur_statuse() {
        return $this->hasOne(TableStatuse::class, 'table_id', 'id')
            ->where('user_id', auth()->id());
    }

    public function _cur_settings() {
        return $this->hasOne(TableUserSetting::class, 'table_id', 'id')
            ->where('user_id', auth()->id());
    }

    public function _owner_settings() {
        if (!$this->getAttribute('user_id')) {
            throw new \Exception('Table::_owner_settings must not be called in eager loading.');
        }

        return $this->hasOne(TableUserSetting::class, 'table_id', 'id')
            ->where('user_id', $this->getAttribute('user_id'));
    }

    public function _theme() {
        return $this->hasOne(AppTheme::class, 'obj_id', 'id')
            ->where('obj_type', 'table');
    }

    public function _email_addon_settings() {
        return $this->hasOne(TableEmailAddonSetting::class, 'table_id', 'id');
    }


    /* Unit Conversions */
    public function _uc_table() {
        return $this->hasOne(Table::class, 'id', 'unit_conv_table_id');
    }
    /* Unit Conversions */

    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
