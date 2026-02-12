<?php

namespace Vanguard\Models\Table;

use DateTime;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\User\UserApiKey;

/**
 * Vanguard\Models\Table\TableTwilioAddonSetting
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string|null $description
 * @property int $twilio_active
 * @property int|null $acc_twilio_key_id
 * @property int|null $recipient_field_id
 * @property string|null $recipient_phones
 * @property string|null $sms_body
 * @property string $sms_send_time
 * @property DateTime|null $sms_delay_time
 * @property int|null $sms_delay_record_fld_id
 * @property string|null $preview_background_header
 * @property string|null $preview_background_body
 * @property int|null $allow_resending
 * @property int|null $limit_row_group_id
 * @property int|null $prepared_sms
 * @property int|null $sent_sms
 * @property string|null $hash
 * @property-read Table $_table
 * @property-read TableRowGroup|null $_limit_row_group
 * @property-read TableField|null $_recipient_field
 * @property-read TableField|null $_sms_delay_record_field
 * @property-read UserApiKey|null $_acc_twilio_key
 * @property-read Collection|TableTwilioAddonHistory[] $_history_sms
 * @mixin Eloquent
 */
class TableTwilioAddonSetting extends Model
{
    public $timestamps = false;
    protected $table = 'table_twilio_addon_settings';
    protected $fillable = [
        'table_id',
        'name',
        'description',
        'twilio_active',
        'acc_twilio_key_id',
        'recipient_field_id',
        'recipient_phones',
        'sms_body',
        'sms_send_time',
        'sms_delay_time',
        'sms_delay_record_fld_id',
        'preview_background_header',
        'preview_background_body',
        'allow_resending',
        'limit_row_group_id',
        'prepared_sms',
        'sent_sms',
        'hash',
    ];


    //specific functions
    public function notInProgressSms()
    {
        return $this->prepared_sms == 0 || $this->prepared_sms == $this->sent_sms;
    }

    public function startPreparedSms(int $num, bool $add = false)
    {
        if ($add) {
            $this->prepared_sms += $num;
        } else {
            $this->prepared_sms = $num;
            $this->sent_sms = 0;
        }
        $this->save();
    }

    public function cancelQueueSms()
    {
        $this->prepared_sms = 0;
        $this->sent_sms = 0;
        $this->save();
    }

    public function oneFinishedSms()
    {
        $this->refresh();
        if ($this->sent_sms < $this->prepared_sms) {
            $this->sent_sms += 1;
            $this->save();
        }
        if ($this->sent_sms == $this->prepared_sms) {
            sleep(3);
            $this->prepared_sms = 0;
            $this->sent_sms = 0;
            $this->save();
        }
    }
    //-------------


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_twilio_rights', 'table_twilio_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_twilio_id', 'table_permission_id', 'can_edit']);
    }
    public function _twilio_rights() {
        return $this->hasMany(TableTwilioRight::class, 'table_twilio_id', 'id');
    }

    public function _acc_twilio_key()
    {
        return $this->hasOne(UserApiKey::class, 'id', 'acc_twilio_key_id');
    }

    public function _recipient_field()
    {
        return $this->hasOne(TableField::class, 'id', 'recipient_field_id');
    }

    public function _sms_delay_record_field()
    {
        return $this->hasOne(TableField::class, 'id', 'sms_delay_record_fld_id');
    }

    public function _limit_row_group()
    {
        return $this->hasOne(TableRowGroup::class, 'id', 'limit_row_group_id');
    }

    public function _history_sms()
    {
        return $this->hasMany(TableTwilioAddonHistory::class, 'table_twilio_addon_id', 'id')
            ->where('msg_type', '=', 'sms');
    }

}
