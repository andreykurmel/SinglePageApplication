<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;

/**
 * Vanguard\Models\Table\TableAlert
 *
 * @property int $id
 * @property int $table_id
 * @property int|null $user_id
 * @property string $name
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
 * @property int|null $on_added_ref_cond_id
 * @property int|null $on_updated_ref_cond_id
 * @property int|null $on_deleted_ref_cond_id
 * @property string|null $description
 * @property int $ask_anr_confirmation
 * @property string|null $cc_recipients
 * @property string|null $bcc_recipients
 * @property int|null $cc_row_mail_field_id
 * @property int|null $bcc_row_mail_field_id
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_added_ref_cond
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertRight[] $_alert_rights
 * @property-read int|null $_alert_rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertAnrTable[] $_anr_tables
 * @property-read int|null $_anr_tables_count
 * @property-read \Vanguard\Models\Table\TableField|null $_bcc_row_mail_field
 * @property-read \Vanguard\Models\Table\TableField|null $_cc_row_mail_field
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup|null $_col_group
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableAlertCondition[] $_conditions
 * @property-read int|null $_conditions_count
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_deleted_ref_cond
 * @property-read \Vanguard\Models\Table\TableField|null $_row_mail_field
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\DataSetPermissions\TablePermission[] $_table_permissions
 * @property-read int|null $_table_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\AlertUfvTable[] $_ufv_tables
 * @property-read int|null $_ufv_tables_count
 * @property-read \Vanguard\Models\DataSetPermissions\TableRefCondition|null $_updated_ref_cond
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
        'on_added',
        'on_updated',
        'on_deleted',
        'on_added_ref_cond_id',
        'on_updated_ref_cond_id',
        'on_deleted_ref_cond_id',
        'ask_anr_confirmation',
        'description',

        'recipients',
        'cc_recipients',
        'bcc_recipients',
        'row_mail_field_id',
        'cc_row_mail_field_id',
        'bcc_row_mail_field_id',

        'mail_subject',
        'mail_addressee',
        'mail_message',
        'mail_format', //['table', 'list']
        'mail_col_group_id',
        'mail_delay_hour',
        'mail_delay_min',
        'mail_delay_sec',
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

    public function _alert_rights() {
        return $this->hasMany(TableAlertRight::class, 'table_alert_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_alert_rights', 'table_alert_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_alert_id', 'table_permission_id', 'can_edit', 'can_activate']);
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

    public function _conditions() {
        return $this->hasMany(TableAlertCondition::class, 'table_alert_id', 'id');
    }

    public function _added_ref_cond() {
        return $this->hasOne(TableRefCondition::class, 'id', 'on_added_ref_cond_id');
    }
    public function _updated_ref_cond() {
        return $this->hasOne(TableRefCondition::class, 'id', 'on_updated_ref_cond_id');
    }
    public function _deleted_ref_cond() {
        return $this->hasOne(TableRefCondition::class, 'id', 'on_deleted_ref_cond_id');
    }

    public function _anr_tables() {
        return $this->hasMany(AlertAnrTable::class, 'table_alert_id', 'id');
    }
    public function _ufv_tables() {
        return $this->hasMany(AlertUfvTable::class, 'table_alert_id', 'id');
    }

    public function _col_group() {
        return $this->belongsTo(TableColumnGroup::class, 'mail_col_group_id', 'id');
    }
}
