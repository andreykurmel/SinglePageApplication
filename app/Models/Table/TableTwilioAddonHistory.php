<?php

namespace Vanguard\Models\Table;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableTwilioAddonHistory
 *
 * @property int $id
 * @property int $table_email_addon_id
 * @property int $row_id
 * @property string $msg_type
 * @property Carbon $send_date
 * @property string $preview_from
 * @property string $preview_to
 * @property string|null $preview_body
 * @property string $preview_row
 * @mixin Eloquent
 * @property-read TableTwilioAddonSetting|null $_addon
 */
class TableTwilioAddonHistory extends Model
{
    public $timestamps = false;

    protected $table = 'table_twilio_addon_history';

    protected $fillable = [
        'table_twilio_addon_id',
        'row_id',
        'msg_type', // ['sms']
        'send_date',
        'preview_from',
        'preview_to',
        'preview_body',
        'preview_row',
    ];

    /**
     * @return array
     */
    public function decodeArrays(): array
    {
        $this->preview_to = json_decode($this->preview_to, true);
        $this->preview_row = json_decode($this->preview_row, true);
        return $this->toArray();
    }


    public function _addon()
    {
        return $this->belongsTo(TableTwilioAddonSetting::class, 'table_twilio_addon_id', 'id');
    }
}
