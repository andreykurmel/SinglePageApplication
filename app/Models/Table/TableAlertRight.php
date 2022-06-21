<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableAlertRight
 *
 * @property int $id
 * @property int $table_alert_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property int $can_activate
 * @property-read \Vanguard\Models\Table\TableAlert $_alert
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @mixin \Eloquent
 */
class TableAlertRight extends Model
{
    protected $table = 'table_alert_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_alert_id',
        'table_permission_id',
        'can_edit',
        'can_activate',
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _alert() {
        return $this->belongsTo(TableAlert::class, 'table_alert_id', 'id');
    }
}
