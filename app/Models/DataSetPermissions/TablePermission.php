<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
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

class TablePermission extends Model
{
    protected $table = 'table_permissions';

    public $timestamps = false;

    protected $fillable = [
        'is_system',
        'table_id',
        'active',
        'name',
        'notes',
        'can_add',
        'can_download',// string of 7 digits '0110000' -> ['print','csv','pdf','xls','json','xml','png']
        'can_see_history',
        'can_reference',
        'referencing_shared',
        'can_create_view',
        'can_create_condformat',
        'can_see_datatab',
        'can_edit_tb',
        'can_drag_rows',
        'can_drag_columns',
        'datatab_methods',// string of 6 digits '011000' -> ['scratch','csv','mysql','remote','reference','paste']
        'datatab_only_append',
        'link_hash',
        'pass',
        'row_request',
        'is_request',
        'user_link',
        'enforced_theme',
        'dcr_hash',

        'dcr_sec_background_by',
        'dcr_sec_scroll_style',
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

        'one_per_submission',
        'dcr_record_status_id',
        'dcr_record_url_field_id',
        'dcr_record_allow_unfinished',
        'dcr_record_visibility_id',
        'dcr_record_visibility_def',
        'dcr_record_editability_id',
        'dcr_record_editability_def',

        //submission
        'dcr_confirm_msg',
        'dcr_email_field_id',
        'dcr_email_field_static',
        'dcr_email_subject',
        'dcr_addressee_field_id',
        'dcr_addressee_txt',
        'dcr_email_message',
        'dcr_email_format',
        'dcr_email_col_group_id',
        //save
        'dcr_save_confirm_msg',
        'dcr_save_email_field_id',
        'dcr_save_email_field_static',
        'dcr_save_email_subject',
        'dcr_save_addressee_field_id',
        'dcr_save_addressee_txt',
        'dcr_save_email_message',
        'dcr_save_email_format',
        'dcr_save_email_col_group_id',
        //update
        'dcr_upd_confirm_msg',
        'dcr_upd_email_field_id',
        'dcr_upd_email_field_static',
        'dcr_upd_email_subject',
        'dcr_upd_addressee_field_id',
        'dcr_upd_addressee_txt',
        'dcr_upd_email_message',
        'dcr_upd_email_format',
        'dcr_upd_email_col_group_id',
    ];

    /**
     * @var array
     */
    public $design_tab = [
        'dcr_sec_background_by',
        'dcr_sec_scroll_style',
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

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->_table->_user;
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
            ->orWhere(function ($query) {
                $query->where('table_permissions.is_request', 0);//is not request
                $query->where('table_permissions.is_system', 1);//applied permission for 'Visitor' if 'public' Table
            });
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
    public function _column_links() {
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

    public function _charts() {
        return $this->belongsToMany(TableChart::class, 'table_chart_rights', 'table_permission_id', 'table_chart_id')
            ->as('_pivot')
            ->withPivot(['can_edit']);
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
