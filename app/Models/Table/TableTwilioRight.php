<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_twilio_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableTwilioAddonSetting $_twilio
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableTwilioRight extends Model
{
    protected $table = 'table_twilio_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_twilio_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _twilio()
    {
        return $this->belongsTo(TableTwilioAddonSetting::class, 'table_twilio_id', 'id');
    }
}
