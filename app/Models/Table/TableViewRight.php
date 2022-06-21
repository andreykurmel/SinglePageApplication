<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\User\UserGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableViewRight
 *
 * @property int $id
 * @property int $table_view_id
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int $table_permission_id
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\DataSetPermissions\TablePermission $_table_permission
 * @property-read \Vanguard\Models\Table\TableView $_view
 * @mixin \Eloquent
 */
class TableViewRight extends Model
{
    protected $table = 'table_view_rights';

    public $timestamps = false;

    protected $fillable = [
        'table_view_id',
        'table_permission_id',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table_permission() {
        return $this->belongsTo(TablePermission::class, 'table_permission_id', 'id');
    }

    public function _view() {
        return $this->belongsTo(TableView::class, 'table_view_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
