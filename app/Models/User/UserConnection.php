<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserConnection
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $host
 * @property string $login
 * @property string $pass
 * @property string|null $db
 * @property string|null $table
 * @property string|null $notes
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $row_hash
 * @property int $row_order
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection wherePass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserConnection whereUserId($value)
 * @mixin \Eloquent
 */
class UserConnection extends Model
{
    protected $table = 'user_connections';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'host',
        'login',
        'pass',
        'db',
        'table',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
