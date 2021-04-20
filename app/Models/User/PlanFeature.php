<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class PlanFeature extends Model
{
    protected $table = 'plan_features';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'object_id',
        'q_tables',
        'row_table',
        'data_build',
        'data_csv',
        'data_mysql',
        'data_remote',
        'data_ref',
        'data_paste',
        'data_g_sheet',
        'data_web_scrap',
        'unit_conversion',
        'group_rows',
        'group_columns',
        'group_users',
        'group_refs',
        'link_type_record',
        'link_type_table',
        'link_type_local',
        'link_type_web',
        'ddl_ref',
        'drag_rows',
        'permission_col_view',
        'permission_col_edit',
        'permission_row_view',
        'permission_row_edit',
        'permission_row_add',
        'permission_row_del',
        'permission_views',
        'permission_cond_format',
        'dwn_print',
        'dwn_csv',
        'dwn_pdf',
        'dwn_xls',
        'dwn_json',
        'dwn_xml',
        'can_google_autocomplete',
        'form_visibility',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
