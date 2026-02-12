<?php

namespace Vanguard\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\HistoryField
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $table_id
 * @property int|null $table_field_id
 * @property int|null $row_id
 * @property string $receiver
 * @property string $incoming_app_id
 * @property array $content
 * @property int $missed
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property-read Table $_table
 * @property-read TableField|null $_table_field
 * @property-read User $_user
 * @mixin Eloquent
 */
class TwilioHistory extends Model
{
    public static $EMAIL_TYPE = 'email';
    public static $SMS_TYPE = 'sms';
    public static $CALL_TYPE = 'call';

    public $timestamps = false;

    protected $table = 'twilio_history';

    protected $fillable = [
        'user_id',
        'table_id',
        'table_field_id',
        'row_id',
        'type', // [email, sms, call]
        'receiver',
        'incoming_app_id',
        'content',
        'missed',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    protected $casts = [
        'content' => 'array',
    ];


    public function _user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_field()
    {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
