<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableAiRight
 *
 * @property int $id
 * @property int $table_ai_id
 * @property int $table_permission_id
 * @property int $can_edit
 * @property-read TableAi $_ai
 * @property-read TablePermission $_table_permission
 * @mixin Eloquent
 */
class TableAiRight extends Model
{
    protected $table = 'table_ai_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_ai_id',
        'table_permission_id',
        'can_edit'
    ];


    public function _table_permission()
    {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _ai()
    {
        return $this->belongsTo(TableAi::class, 'table_ai_id', 'id');
    }
}
