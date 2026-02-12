<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableChartRight
 *
 * @property int $id
 * @property int $table_email_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableEmailAddonSetting $_email
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableEmailRight extends Model
{
    protected $table = 'table_email_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_email_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _email()
    {
        return $this->belongsTo(TableEmailAddonSetting::class, 'table_email_id', 'id');
    }
}
