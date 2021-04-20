<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TablePermissionForbidSettings extends Model
{
    protected $table = 'table_permission_forbid_settings';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'db_col_name',
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }
}
