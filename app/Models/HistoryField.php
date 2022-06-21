<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\HistoryField
 *
 * @property int $id
 * @property int $user_id
 * @property int $table_id
 * @property int|null $table_field_id
 * @property int $row_id
 * @property string|null $value
 * @property int|null $to_user_id
 * @property string|null $comment
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\Models\Table\TableField|null $_table_field
 * @property-read \Vanguard\User $_user
 * @property-read \Vanguard\User|null $_to_user
 * @mixin \Eloquent
 */
class HistoryField extends Model
{
    protected $table = 'history';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'table_id',
        'table_field_id',
        'row_id',
        'value',
        'to_user_id',
        'comment',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function _to_user() {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
