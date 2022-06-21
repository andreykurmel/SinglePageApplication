<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\PlanFeature
 *
 * @property int $id
 * @property string $type
 * @property int $object_id
 * @property string|null $q_tables
 * @property string|null $row_table
 * @property int|null $data_storage_backup
 * @property int|null $data_build
 * @property int|null $data_csv
 * @property int|null $data_mysql
 * @property int|null $data_remote
 * @property int|null $data_ref
 * @property int|null $data_paste
 * @property int|null $data_g_sheets
 * @property int|null $data_web_scrap
 * @property int|null $unit_conversions
 * @property int|null $group_rows
 * @property int|null $group_columns
 * @property int|null $group_refs
 * @property int|null $link_type_record
 * @property int|null $link_type_web
 * @property int|null $link_type_app
 * @property int|null $ddl_ref
 * @property int|null $drag_rows
 * @property int|null $permission_col_view
 * @property int|null $permission_col_edit
 * @property int|null $permission_row_view
 * @property int|null $permission_row_edit
 * @property int|null $permission_row_add
 * @property int|null $permission_row_del
 * @property int|null $permission_views
 * @property int|null $permission_cond_format
 * @property int|null $dwn_print
 * @property int|null $dwn_csv
 * @property int|null $dwn_pdf
 * @property int|null $dwn_xls
 * @property int|null $dwn_json
 * @property int|null $dwn_xml
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $alerts_module
 * @property string|null $row_hash
 * @property int|null $can_google_autocomplete
 * @property int|null $form_visibility
 * @property int|null $field_type_user
 * @property int|null $apps_are_avail
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereAlertsModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereAppsAreAvail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereCanGoogleAutocomplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataBuild($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataCsv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataGSheets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataMysql($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataPaste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataRemote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataStorageBackup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDataWebScrap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDdlRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDragRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnCsv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnPdf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnPrint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnXls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereDwnXml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereFieldTypeUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereFormVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereGroupColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereGroupRefs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereGroupRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereLinkTypeApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereLinkTypeRecord($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereLinkTypeWeb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionColEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionColView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionCondFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionRowAdd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionRowDel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionRowEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionRowView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature wherePermissionViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereQTables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereRowTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\PlanFeature whereUnitConversions($value)
 * @mixin \Eloquent
 */
class PlanFeature extends Model
{
    protected $table = 'plan_features';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'object_id',
        'q_tables',
        'row_table',
        'data_storage_backup',
        'data_build',
        'data_csv',
        'data_mysql',
        'data_remote',
        'data_ref',
        'data_paste',
        'data_g_sheets',
        'data_web_scrap',
        'unit_conversion',
        'group_rows',
        'group_columns',
        'group_refs',
        'link_type_record',
        'link_type_web',
        'link_type_app',
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
        'ddl_ref',
        'drag_rows',
        'can_google_autocomplete',
        'form_visibility',
        'field_type_user',
        'apps_are_avail',

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
