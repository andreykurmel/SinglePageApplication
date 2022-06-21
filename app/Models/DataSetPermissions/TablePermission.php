<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertRight;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableChartRight;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLinkToDcr;
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
 * @property int $referencing_shared
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\Addon[] $_addons
 * @property-read int|null $_addons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertRight[] $_alert_rights
 * @property-read int|null $_alert_rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlert[] $_alerts
 * @property-read int|null $_alerts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableChartRight[] $_chart_rights
 * @property-read int|null $_chart_rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableChart[] $_charts
 * @property-read int|null $_charts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableColumnGroup[] $_column_groups
 * @property-read int|null $_column_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\CondFormat[] $_cond_formats
 * @property-read int|null $_cond_formats_count
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
 * @property-read int|null $_default_fields_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionForbidSettings[] $_forbid_settings
 * @property-read int|null $_forbid_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableFieldLinkToDcr[] $_link_limits
 * @property-read int|null $_link_limits_count
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionColumn[] $_permission_columns
 * @property-read int|null $_permission_columns_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionRow[] $_permission_rows
 * @property-read int|null $_permission_rows_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TableRowGroup[] $_row_groups
 * @property-read int|null $_row_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermissionRow[] $_row_links
 * @property-read int|null $_row_links_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup2TablePermission[] $_shared_tables
 * @property-read int|null $_shared_tables_count
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup[] $_user_groups
 * @property-read int|null $_user_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableViewRight[] $_view_rights
 * @property-read int|null $_view_rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableView[] $_views
 * @property-read int|null $_views_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission applyIsActiveForUserOrPermission($table_permission_id = null, $visitor_scope = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission isActiveForSelectedUser($user_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission isActiveForUser()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission isActiveForUserOrVisitor()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission isActiveForVisitor()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanAdd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanCreateCondformat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanCreateView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanDownload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanDragColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanDragRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanEditTb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanPublicCopy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanSeeDatatab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereCanSeeHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereDatatabMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereDatatabOnlyAppend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereEnforcedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereHideFolderStructure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereReferencingShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TablePermission whereTableId($value)
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
        'can_reference', // table available for using in DDL by Collaborators
        'can_public_copy',
        'referencing_shared', // Collaborator can apply RefCondition with this table
        'can_create_view',
        'can_create_condformat',
        'can_see_datatab',
        'can_edit_tb',
        'can_drag_rows',
        'can_drag_columns',
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
    public function _alert_rights() {
        return $this->hasMany(TableAlertRight::class, 'table_permission_id', 'id');
    }

    public function _charts() {
        return $this->belongsToMany(TableChart::class, 'table_chart_rights', 'table_permission_id', 'table_chart_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
    }
    public function _alerts() {
        return $this->belongsToMany(TableAlert::class, 'table_alert_rights', 'table_permission_id', 'table_alert_id')
            ->as('_pivot')
            ->withPivot(['can_edit','can_activate']);
    }

    public function _addons() {
        return $this->belongsToMany(Addon::class, 'table_permissions_2_addons', 'table_permission_id', 'addon_id')
            ->as('_link')
            ->withPivot(['id', 'addon_id', 'type', 'lockout_layout', 'add_new', 'hide_settings', 'block_spacing', 'vert_grid_step']);
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
