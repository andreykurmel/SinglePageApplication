<?php

namespace Vanguard\Models\Table;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableEmailAddonSetting
 *
 * @property int $id
 * @property int $table_email_addon_id
 * @property int $row_id
 * @property Carbon $send_date
 * @property string $preview_from
 * @property string $preview_to
 * @property string|null $preview_cc
 * @property string|null $preview_bcc
 * @property string|null $preview_reply
 * @property string|null $preview_subject
 * @property string|null $preview_body
 * @property string $preview_tablda_row
 * @mixin Eloquent
 * @property-read TableEmailAddonSetting|null $_addon
 */
class TableEmailAddonHistory extends Model
{
    public $timestamps = false;

    protected $table = 'table_email_addon_history';

    protected $fillable = [
        'table_email_addon_id',
        'row_id',
        'send_date',
        'preview_from',
        'preview_to',
        'preview_cc',
        'preview_bcc',
        'preview_reply',
        'preview_subject',
        'preview_body',
        'preview_tablda_row',
    ];

    /**
     * @return array
     */
    public function decodeArrays(): array
    {
        $this->preview_to = json_decode($this->preview_to, true);
        $this->preview_cc = json_decode($this->preview_cc, true);
        $this->preview_bcc = json_decode($this->preview_bcc, true);
        $this->preview_reply = json_decode($this->preview_reply, true);
        $this->preview_tablda_row = json_decode($this->preview_tablda_row, true);
        return $this->toArray();
    }


    public function _addon()
    {
        return $this->belongsTo(TableEmailAddonSetting::class, 'table_email_addon_id', 'id');
    }
}
