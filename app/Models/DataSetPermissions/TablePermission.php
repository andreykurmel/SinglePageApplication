<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Dcr\TableDataRequestRight;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertRight;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableCalendarRight;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartRight;
use Vanguard\Models\Table\TableEmailAddonSetting;
use Vanguard\Models\Table\TableEmailRight;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLinkToDcr;
use Vanguard\Models\Table\TableGantt;
use Vanguard\Models\Table\TableGanttRight;
use Vanguard\Models\Table\TableGrouping;
use Vanguard\Models\Table\TableKanbanRight;
use Vanguard\Models\Table\TableKanbanSettings;
use Vanguard\Models\Table\TableMap;
use Vanguard\Models\Table\TableMapRight;
use Vanguard\Models\Table\TableReport;
use Vanguard\Models\Table\TableReportRight;
use Vanguard\Models\Table\TableSimplemap;
use Vanguard\Models\Table\TableSimplemapRight;
use Vanguard\Models\Table\TableTournament;
use Vanguard\Models\Table\TableTournamentRight;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Models\Table\TableTwilioRight;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\Table\TableViewRight;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TablePermission;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\User;


/**
 * Vanguard\Models\DataSetPermissions\TablePermission
 *
 * @property int $id
 * @property int $table_id
 * @property string|null $name
 * @property string|null $notes
 * @property int $can_add
 * @property int $can_delete
 * @property string $can_download
 * @property int $can_see_history
 * @property int $can_reference
 * @property int $can_public_copy
 * @property int $can_create_view
 * @property int $can_create_condformat
 * @property int $hide_folder_structure
 * @property int $is_system
 * @property int $can_see_datatab
 * @property string $datatab_methods
 * @property int $datatab_only_append
 * @property int $can_edit_tb
 * @property int $can_drag_rows
 * @property int $enforced_theme
 * @property int $can_drag_columns
 * @property int $can_change_primaryview
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\Addon[] $_addons
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertRight[] $_alert_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableChartRight[] $_chart_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableKanbanRight[] $_kanban_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableDataRequestRight[] $_dcr_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableReportRight[] $_report_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableGanttRight[] $_gantt_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableMapRight[] $_map_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableCalendarRight[] $_calendar_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableEmailRight[] $_email_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableTwilioRight[] $_twilio_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableTournamentRight[] $_tournament_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|TableSimplemapRight[] $_simplemap_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableChart[] $_charts
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableKanbanSettings[] $_kanbans
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlert[] $_alerts
 * @property-read \Illuminate\Database\Eloquent\Collection|TableDataRequest[] $_dcrs
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableReport[] $_reports
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableGantt[] $_gantts
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableMap[] $_maps
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableCalendar[] $_calendars
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableEmailAddonSetting[] $_emails
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableTwilioAddonSetting[] $_twilios
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableTournament[] $_tournaments
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableSimplemap[] $_simplemaps
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableColumnGroup[] $_column_groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\CondFormat[] $_cond_formats
 * @property-read \Vanguard\User $_created_user
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_addressee_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_bcc_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_cc_email_field
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_dcr_email_col_group
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_save_addressee_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_save_bcc_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_save_cc_email_field
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_dcr_save_email_col_group
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_save_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_upd_addressee_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_upd_bcc_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_upd_cc_email_field
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_dcr_upd_email_col_group
 * @property-read \Vanguard\Models\Table\TableField|null $_dcr_upd_email_field
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionDefaultField[] $_default_fields
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings[] $_forbid_settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkToDcr[] $_link_limits
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionColumn[] $_permission_columns
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionRow[] $_permission_rows
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableRowGroup[] $_row_groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionRow[] $_row_links
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup2TablePermission[] $_shared_tables
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup[] $_user_groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableViewRight[] $_view_rights
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableView[] $_views
 * @mixin \Eloquent
 */
class TablePermission extends Model
{
    protected $table = 'table_permissions';

    public $timestamps = false;

    protected $fillable = [
        'is_system',
        'table_id',
        'name',
        'notes',
        'can_add',
        'can_download', // string of 7 digits '0110000' -> ['print','csv','pdf','xls','json','xml','png']
        'can_see_history',
        'can_reference', // table available for using in DDL by Collaborators // Collaborator can apply RefCondition with this table
        'can_public_copy',
        'can_create_view',
        'can_create_condformat',
        'can_see_datatab',
        'can_edit_tb',
        'can_drag_rows',
        'can_drag_columns',
        'can_change_primaryview',
        'hide_folder_structure',
        'datatab_methods', // string of 6 digits '011000' -> ['scratch','csv','mysql','remote','reference','paste']
        'datatab_only_append',
        'enforced_theme',
    ];


    /**
     * Permission is active for current User
     *
     * @param $builder
     * @return mixed
     */
    public function scopeIsActiveForVisitor($builder)
    {
        $builder->where('table_permissions.is_system', 1);//applied permission for 'Visitor' if 'public' Table
        return $builder;
    }


    /**
     * Permission is active for current User
     *
     * @param $builder
     * @return mixed
     */
    public function scopeIsActiveForUser($builder) {
        //user is member of userGroup
        $auth = app( AuthUserSingleton::class );
        return $builder->whereIn('table_permissions.id', $auth->getTablePermissionIdsMember());
    }

    /**
     * Permission is active for current User | Visitor
     *
     * @param $builder
     * @return mixed
     */
    public function scopeIsActiveForUserOrVisitor($builder) {
        return $builder->isActiveForUser() //user is member of userGroup
            ->orWhere('table_permissions.is_system', 1);//applied permission for 'Visitor' if 'public' Table
    }

    /**
     * Permission is active for selected User
     *
     * @param $builder
     * @param $user_id
     * @return mixed
     */
    public function scopeIsActiveForSelectedUser($builder, $user_id) {
        return $builder->whereHas('_user_groups', function ($ug) use ($user_id) {
            $ug->where('user_groups_2_table_permissions.is_active', 1);
            $ug->whereHas('_individuals_all', function ($ia) use ($user_id) {
                $ia->where('users.id', $user_id);
            });
        });
    }

    /**
     * Permission is active for current User | Visitor | transferred Permission
     *
     * @param $builder
     * @param null $table_permission_id
     * @param bool $visitor_scope
     * @return mixed
     */
    public function scopeApplyIsActiveForUserOrPermission($builder, $table_permission_id = null, $visitor_scope = true) {
        if ($table_permission_id) {
            return $builder->where('table_permissions.id', $table_permission_id);
        } else {
            return $visitor_scope
                ? $builder->isActiveForUserOrVisitor()
                : $builder->isActiveForUser();
        }
    }


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user_groups() {
        return $this
            ->belongsToMany(UserGroup::class, 'user_groups_2_table_permissions', 'table_permission_id', 'user_group_id')
            ->withPivot(['id', 'table_permission_id', 'user_group_id', 'is_active', 'is_app']);
    }

    public function _shared_tables() {
        return $this->hasMany(UserGroup2TablePermission::class, 'table_permission_id', 'id');
    }


    //----- table column groups
    public function _column_groups() {
        return $this->belongsToMany(TableColumnGroup::class, 'table_permissions_2_table_column_groups', 'table_permission_id', 'table_column_group_id')
            ->as('_link')
            ->withPivot(['view', 'edit', 'delete', 'shared']);
    }

    public function _permission_columns() {
        return $this->hasMany(TablePermissionColumn::class, 'table_permission_id', 'id');
    }
    //--------------------------

    //----- table row groups
    public function _row_groups() {
        return $this->belongsToMany(TableRowGroup::class, 'table_permissions_2_table_row_groups', 'table_permission_id', 'table_row_group_id')
            ->as('_link')
            ->withPivot(['view', 'edit', 'delete', 'shared']);
    }

    public function _permission_rows() {
        return $this->hasMany(TablePermissionRow::class, 'table_permission_id', 'id');
    }
    public function _row_links() {
        return $this->hasMany(TablePermissionRow::class, 'table_permission_id', 'id');
    }
    //--------------------------

    public function _cond_formats() {
        return $this->belongsToMany(CondFormat::class, 'table_permissions_2_cond_formats', 'table_permission_id', 'cond_format_id')
            ->as('_pivot')
            ->withPivot(['always_on', 'visible_shared']);
    }

    public function _view_rights() {
        return $this->hasMany(TableViewRight::class, 'table_permission_id', 'id');
    }

    public function _views() {
        return $this->belongsToMany(TableView::class, 'table_view_rights', 'table_permission_id', 'table_view_id');
    }

    public function _chart_rights() {
        return $this->hasMany(TableChartRight::class, 'table_permission_id', 'id');
    }
    public function _kanban_rights() {
        return $this->hasMany(TableKanbanRight::class, 'table_permission_id', 'id');
    }
    public function _alert_rights() {
        return $this->hasMany(TableAlertRight::class, 'table_permission_id', 'id');
    }
    public function _dcr_rights() {
        return $this->hasMany(TableDataRequestRight::class, 'table_permission_id', 'id');
    }
    public function _report_rights() {
        return $this->hasMany(TableReportRight::class, 'table_permission_id', 'id');
    }
    public function _gantt_rights() {
        return $this->hasMany(TableGanttRight::class, 'table_gantt_id', 'id');
    }
    public function _map_rights() {
        return $this->hasMany(TableMapRight::class, 'table_map_id', 'id');
    }
    public function _calendar_rights() {
        return $this->hasMany(TableCalendarRight::class, 'table_calendar_id', 'id');
    }
    public function _email_rights() {
        return $this->hasMany(TableEmailRight::class, 'table_email_id', 'id');
    }
    public function _twilio_rights() {
        return $this->hasMany(TableTwilioRight::class, 'table_twilio_id', 'id');
    }
    public function _tournament_rights() {
        return $this->hasMany(TableTournamentRight::class, 'table_tournament_id', 'id');
    }
    public function _simplemap_rights() {
        return $this->hasMany(TableSimplemapRight::class, 'table_simplemap_id', 'id');
    }

    public function _charts() {
        return $this->belongsToMany(TableChart::class, 'table_chart_rights', 'table_permission_id', 'table_chart_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _kanbans() {
        return $this->belongsToMany(TableKanbanSettings::class, 'table_kanban_rights', 'table_permission_id', 'table_kanban_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _alerts() {
        return $this->belongsToMany(TableAlert::class, 'table_alert_rights', 'table_permission_id', 'table_alert_id')
            ->as('_pivot')
            ->withPivot(['can_edit','can_activate']);
    }
    public function _dcrs() {
        return $this->belongsToMany(TableDataRequest::class, 'table_data_request_rights', 'table_permission_id', 'table_data_request_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _reports() {
        return $this->belongsToMany(TableReport::class, 'table_report_rights', 'table_permission_id', 'table_report_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _gantts() {
        return $this->belongsToMany(TableGantt::class, 'table_gantt_rights', 'table_permission_id', 'table_gantt_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _maps() {
        return $this->belongsToMany(TableMap::class, 'table_map_rights', 'table_permission_id', 'table_map_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _calendars() {
        return $this->belongsToMany(TableCalendar::class, 'table_calendar_rights', 'table_permission_id', 'table_calendar_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _emails() {
        return $this->belongsToMany(TableEmailAddonSetting::class, 'table_email_rights', 'table_permission_id', 'table_email_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _twilios() {
        return $this->belongsToMany(TableTwilioAddonSetting::class, 'table_twilio_rights', 'table_permission_id', 'table_twilio_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _tournaments() {
        return $this->belongsToMany(TableTournament::class, 'table_tournament_rights', 'table_permission_id', 'table_tournament_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _simplemaps() {
        return $this->belongsToMany(TableSimplemap::class, 'table_simplemap_rights', 'table_permission_id', 'table_simplemap_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _groupings() {
        return $this->belongsToMany(TableGrouping::class, 'table_grouping_rights', 'table_permission_id', 'table_grouping_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }

    public function _addons() {
        return $this->belongsToMany(Addon::class, 'table_permissions_2_addons', 'table_permission_id', 'addon_id')
            ->as('_link')
            ->withPivot(['id', 'addon_id', 'type', 'lockout_layout', 'add_new', 'hide_settings', 'block_spacing', 'vert_grid_step', 'crnr_radius']);
    }

    public function _default_fields() {
        return $this->hasMany(TablePermissionDefaultField::class, 'table_permission_id', 'id');
    }

    public function _forbid_settings() {
        return $this->hasMany(TablePermissionForbidSettings::class, 'permission_id', 'id');
    }

    public function _link_limits() {
        return $this->hasMany(TableFieldLinkToDcr::class, 'table_dcr_id', 'id');
    }

    //submission
    public function _dcr_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_email_field_id');
    }
    public function _dcr_cc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_cc_email_field_id');
    }
    public function _dcr_bcc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_bcc_email_field_id');
    }
    public function _dcr_addressee_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_addressee_field_id');
    }
    public function _dcr_email_col_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_email_col_group_id');
    }
    //save
    public function _dcr_save_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_email_field_id');
    }
    public function _dcr_save_cc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_cc_email_field_id');
    }
    public function _dcr_save_bcc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_bcc_email_field_id');
    }
    public function _dcr_save_addressee_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_save_addressee_field_id');
    }
    public function _dcr_save_email_col_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_save_email_col_group_id');
    }
    //update
    public function _dcr_upd_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_email_field_id');
    }
    public function _dcr_upd_cc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_cc_email_field_id');
    }
    public function _dcr_upd_bcc_email_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_bcc_email_field_id');
    }
    public function _dcr_upd_addressee_field() {
        return $this->hasOne(TableField::class, 'id', 'dcr_upd_addressee_field_id');
    }
    public function _dcr_upd_email_col_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'dcr_upd_email_col_group_id');
    }



    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
