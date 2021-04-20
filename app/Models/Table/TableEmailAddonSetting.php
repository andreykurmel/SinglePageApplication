<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserEmailAccount;

class TableEmailAddonSetting extends Model
{
    protected $table = 'table_email_addon_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
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
        'recipient_email',
        'email_subject',
        'email_body',
        'limit_row_group_id',
        'prepared_emails',
        'sent_emails',
        'hash',
    ];


    //specific functions
    public function notInProgress()
    {
        return $this->prepared_emails == $this->sent_emails;
    }

    public function startPrepared(int $num)
    {
        $this->prepared_emails = $num;
        $this->sent_emails = 0;
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

    public function _limit_row_group() {
        return $this->hasOne(TableRowGroup::class, 'id', 'limit_row_group_id');
    }
}
