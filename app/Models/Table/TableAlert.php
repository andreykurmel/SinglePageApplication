<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRowGroup;

/**
 * Vanguard\Models\Table\TableAlert
 *
 * @property int $id
 * @property int $table_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $execution_delay
 * @property string|null $recipients
 * @property int|null $is_active
 * @property int|null $mail_col_group_id
 * @property string $mail_format
 * @property int|null $row_mail_field_id
 * @property int|null $on_added
 * @property int|null $on_updated
 * @property int|null $on_deleted
 * @property string|null $mail_subject
 * @property string|null $mail_addressee
 * @property string|null $mail_message
 * @property int|null $mail_delay_hour
 * @property int|null $mail_delay_min
 * @property int|null $mail_delay_sec
 * @property int|null $on_added_row_group_id
 * @property int|null $on_updated_row_group_id
 * @property int|null $on_deleted_row_group_id
 * @property int|null $automation_email_addon_id
 * @property int|null $row_sms_field_id
 * @property string|null $sms_recipients
 * @property string|null $sms_body
 * @property int|null $sms_delay_hour
 * @property int|null $sms_delay_min
 * @property int|null $sms_delay_sec
 * @property string|null $description
 * @property int $ask_anr_confirmation
 * @property string|null $cc_recipients
 * @property string|null $bcc_recipients
 * @property int|null $cc_row_mail_field_id
 * @property int|null $bcc_row_mail_field_id
 * @property string|null $click_success_message
 * @property string|null $click_introduction
 * @property int|null $notif_email_add_tabledata
 * @property int|null $notif_email_add_clicklink
 * @property int|null $on_snapshot
 * @property string $snapshot_onetime_datetime
 * @property string $snapshot_type
 * @property string $snapshot_timezone
 * @property string $snapshot_frequency
 * @property string $snapshot_hourly_freq
 * @property string $snapshot_day_freq
 * @property string $snapshot_month_freq
 * @property int $snapshot_month_day
 * @property string|null $snapshot_month_date
 * @property string $snapshot_time
 * @property string|null $snp_name
 * @property int|null $snp_field_id_name
 * @property int|null $snp_field_id_time
 * @property int|null $snp_src_table_id
 * @property int|null $snp_row_group_id
 * @property int|null $enabled_email
 * @property int|null $enabled_sms
 * @property int|null $enabled_ufv
 * @property int|null $enabled_anr
 * @property int|null $enabled_sending
 * @property int|null $enabled_snapshot
 * @property string|null $snp_data_range
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_added_row_group
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertRight[] $_alert_rights
 * @property-read int|null $_alert_rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertAnrTable[] $_anr_tables
 * @property-read int|null $_anr_tables_count
 * @property-read \Vanguard\Models\Table\TableField|null $_bcc_row_mail_field
 * @property-read \Vanguard\Models\Table\TableField|null $_cc_row_mail_field
 * @property-read \Vanguard\Models\Table\TableEmailAddonSetting|null $_email_addon
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_col_group
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertCondition[] $_conditions
 * @property-read int|null $_conditions_count
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_deleted_row_group
 * @property-read \Vanguard\Models\Table\TableField|null $_row_mail_field
 * @property-read \Vanguard\Models\Table\TableField|null $_row_sms_field
 * @property-read \Vanguard\Models\Table\TableField|null $_snp_field_name
 * @property-read \Vanguard\Models\Table\TableField|null $_snp_field_time
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\Models\Table\Table|null $_snp_source_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property-read int|null $_table_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertUfvTable[] $_ufv_tables
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertSnapshotField[] $_snapshot_fields
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertClickUpdate[] $_click_updates
 * @property-read int|null $_ufv_tables_count
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_updated_row_group
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Table\TableAlert isAvailForUser($user_id = null)
 * @mixin \Eloquent
 */
class TableAlert extends Model
{
    protected $table = 'table_alerts';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'is_active',
        'execution_delay',
        'on_added',
        'on_updated',
        'on_deleted',
        'on_added_row_group_id',
        'on_updated_row_group_id',
        'on_deleted_row_group_id',
        'automation_email_addon_id',
        'ask_anr_confirmation',
        'description',

        'recipients',
        'cc_recipients',
        'bcc_recipients',
        'row_mail_field_id',
        'cc_row_mail_field_id',
        'bcc_row_mail_field_id',

        'row_sms_field_id',
        'sms_recipients',
        'sms_body',
        'sms_delay_hour',
        'sms_delay_min',
        'sms_delay_sec',

        'click_success_message',
        'click_introduction',
        'notif_email_add_tabledata',
        'notif_email_add_clicklink',

        'on_snapshot',
        'snapshot_onetime_datetime',
        'snapshot_type',//recurring, one_time
        'snapshot_timezone',
        'snapshot_frequency',
        'snapshot_day_freq',
        'snapshot_hourly_freq',
        'snapshot_month_freq',
        'snapshot_month_day',
        'snapshot_month_date',
        'snapshot_time',
        'snp_name',
        'snp_field_id_name',
        'snp_field_id_time',
        'snp_src_table_id',
        'snp_row_group_id',
        'snp_data_range',

        'enabled_email',
        'enabled_sms',
        'enabled_ufv',
        'enabled_anr',
        'enabled_sending',
        'enabled_snapshot',

        'mail_subject',
        'mail_addressee',
        'mail_message',
        'mail_format', //['table', 'list']
        'mail_col_group_id',
        'mail_delay_hour',
        'mail_delay_min',
        'mail_delay_sec',
    ];

    protected $casts = [
        'snapshot_day_freq' => 'array',
    ];

    /**
     * Permission is active for selected User
     *
     * @param $builder
     * @param $user_id
     * @return mixed
     */
    public function scopeIsAvailForUser($builder, $user_id = null)
    {
        return $builder->where(function ($w) use ($user_id) {
            $w->where('user_id', '=', $user_id);
            $w->orWhereHas('_table_permissions', function ($tp) {
                $tp->isActiveForUserOrVisitor();
                $tp->where('can_activate', '=', 1);
            });
        });
    }



    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _snp_source_table() {
        return $this->belongsTo(Table::class, 'snp_src_table_id', 'id');
    }

    public function _alert_rights() {
        return $this->hasMany(TableAlertRight::class, 'table_alert_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_alert_rights', 'table_alert_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_alert_id', 'table_permission_id', 'can_edit', 'can_activate']);
    }

    public function _row_sms_field() {
        return $this->hasOne(TableField::class, 'id', 'row_sms_field_id');
    }

    public function _snp_field_name() {
        return $this->hasOne(TableField::class, 'id', 'snp_field_id_name');
    }

    public function _snp_field_time() {
        return $this->hasOne(TableField::class, 'id', 'snp_field_id_time');
    }

    public function _row_mail_field() {
        return $this->hasOne(TableField::class, 'id', 'row_mail_field_id');
    }
    public function _cc_row_mail_field() {
        return $this->hasOne(TableField::class, 'id', 'cc_row_mail_field_id');
    }
    public function _bcc_row_mail_field() {
        return $this->hasOne(TableField::class, 'id', 'bcc_row_mail_field_id');
    }

    public function _email_addon() {
        return $this->hasOne(TableEmailAddonSetting::class, 'id', 'automation_email_addon_id');
    }

    public function _conditions() {
        return $this->hasMany(TableAlertCondition::class, 'table_alert_id', 'id');
    }

    public function _snapshot_fields() {
        return $this->hasMany(TableAlertSnapshotField::class, 'table_alert_id', 'id');
    }

    public function _added_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'on_added_row_group_id');
    }
    public function _updated_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'on_updated_row_group_id');
    }
    public function _deleted_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'on_deleted_row_group_id');
    }

    public function _anr_tables() {
        return $this->hasMany(AlertAnrTable::class, 'table_alert_id', 'id');
    }
    public function _ufv_tables() {
        return $this->hasMany(AlertUfvTable::class, 'table_alert_id', 'id');
    }
    public function _click_updates() {
        return $this->hasMany(AlertClickUpdate::class, 'table_alert_id', 'id');
    }

    public function _col_group() {
        return $this->belongsTo(TableColumnGroup::class, 'mail_col_group_id', 'id');
    }
}
