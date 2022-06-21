<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserEmailAccount;

/**
 * Vanguard\Models\Table\TableEmailAddonSetting
 *
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string|null $description
 * @property string $server_type
 * @property string $smtp_key_mode
 * @property string|null $google_email
 * @property string|null $google_app_pass
 * @property string|null $sendgrid_api_key
 * @property int|null $acc_sendgrid_key_id
 * @property int|null $acc_google_key_id
 * @property string|null $sender_name
 * @property string|null $sender_email
 * @property int|null $sender_email_isdif
 * @property int|null $sender_email_fld_id
 * @property string|null $sender_reply_to
 * @property int|null $sender_reply_to_isdif
 * @property int|null $sender_reply_to_fld_id
 * @property int|null $recipient_field_id
 * @property string|null $recipient_email
 * @property string|null $email_subject
 * @property string|null $email_body
 * @property string $email_link_width_type
 * @property int $email_link_width_size
 * @property string $email_link_align
 * @property int|null $limit_row_group_id
 * @property int|null $field_id_attachments
 * @property int|null $prepared_emails
 * @property int|null $sent_emails
 * @property string $email_send_time
 * @property int|null $email_delay_by_rec
 * @property \DateTime|null $email_delay_time
 * @property int|null $email_delay_record_fld_id
 * @property string|null $hash
 * @property int|null $cc_recipient_field_id
 * @property int|null $bcc_recipient_field_id
 * @property string|null $cc_recipient_email
 * @property string|null $bcc_recipient_email
 * @property string|null $email_background_header
 * @property string|null $email_background_body
 * @property int|null $allow_resending
 * @property-read \Vanguard\Models\Table\TableField|null $_bcc_recipient_field
 * @property-read \Vanguard\Models\Table\TableField|null $_cc_recipient_field
 * @property-read \Vanguard\Models\User\UserEmailAccount|null $_google_key
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup|null $_limit_row_group
 * @property-read \Vanguard\Models\Table\TableField|null $_recipient_field
 * @property-read \Vanguard\Models\Table\TableField|null $_sender_email_field
 * @property-read \Vanguard\Models\Table\TableField|null $_sender_reply_to_field
 * @property-read \Vanguard\Models\User\UserApiKey|null $_sendgrid_key
 * @property-read \Vanguard\Models\Table\Table $_table
 * @mixin \Eloquent
 * @property-read \Vanguard\Models\Table\TableField|null $_email_delay_record_fld
 * @property-read \Vanguard\Models\Table\TableField|null $_field_for_attachments
 * @property-read Collection|TableEmailAddonHistory[] $_history_emails
 */
class TableEmailAddonSetting extends Model
{
    protected $table = 'table_email_addon_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'description',
        'server_type', // ['google','sendgrid']
        'smtp_key_mode', // ['account','table']
        'google_email',
        'google_app_pass',
        'sendgrid_api_key',
        'acc_sendgrid_key_id',
        'acc_google_key_id',
        'sender_name',
        'sender_email',
        'sender_email_isdif',
        'sender_email_fld_id',
        'sender_reply_to',
        'sender_reply_to_isdif',
        'sender_reply_to_fld_id',
        'recipient_field_id',
        'cc_recipient_field_id',
        'bcc_recipient_field_id',
        'recipient_email',
        'cc_recipient_email',
        'bcc_recipient_email',
        'email_subject',
        'email_body',
        'limit_row_group_id',
        'field_id_attachments',
        'prepared_emails',
        'sent_emails',
        'email_send_time',
        'email_delay_by_rec',
        'email_delay_time',
        'email_delay_record_fld_id',
        'email_link_width_type', // [full, content, column_size, total_size]
        'email_link_width_size',
        'email_link_align', // [left, center, right]
        'email_background_header',
        'email_background_body',
        'allow_resending',
        'hash',
    ];


    //specific functions
    public function notInProgress()
    {
        return $this->prepared_emails == 0 || $this->prepared_emails == $this->sent_emails;
    }

    public function startPrepared(int $num, bool $add = false)
    {
        if ($add) {
            $this->prepared_emails += $num;
        } else {
            $this->prepared_emails = $num;
            $this->sent_emails = 0;
        }
        $this->save();
    }

    public function cancelQueue()
    {
        $this->prepared_emails = 0;
        $this->sent_emails = 0;
        $this->save();
    }

    public function oneFinished()
    {
        $this->refresh();
        if ($this->sent_emails < $this->prepared_emails) {
            $this->sent_emails += 1;
            $this->save();
        }
        if ($this->sent_emails == $this->prepared_emails) {
            sleep(3);
            $this->prepared_emails = 0;
            $this->sent_emails = 0;
            $this->save();
        }
    }
    //-------------



    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _history_emails() {
        return $this->hasMany(TableEmailAddonHistory::class, 'table_email_addon_id', 'id');
    }

    public function _sendgrid_key() {
        return $this->hasOne(UserApiKey::class, 'id', 'acc_sendgrid_key_id');
    }

    public function _google_key() {
        return $this->hasOne(UserEmailAccount::class, 'id', 'acc_google_key_id');
    }

    public function _sender_email_field() {
        return $this->hasOne(TableField::class, 'id', 'sender_email_fld_id');
    }

    public function _sender_reply_to_field() {
        return $this->hasOne(TableField::class, 'id', 'sender_reply_to_fld_id');
    }

    public function _recipient_field() {
        return $this->hasOne(TableField::class, 'id', 'recipient_field_id');
    }
    public function _cc_recipient_field() {
        return $this->hasOne(TableField::class, 'id', 'cc_recipient_field_id');
    }
    public function _bcc_recipient_field() {
        return $this->hasOne(TableField::class, 'id', 'bcc_recipient_field_id');
    }

    public function _limit_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'limit_row_group_id');
    }

    public function _field_for_attachments() {
        return $this->hasOne(TableField::class, 'id', 'field_id_attachments');
    }

    public function _email_delay_record_fld() {
        return $this->hasOne(TableField::class, 'id', 'email_delay_record_fld_id');
    }
}
