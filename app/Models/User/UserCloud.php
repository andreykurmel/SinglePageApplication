<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserCloud
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $cloud
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $row_hash
 * @property string|null $token_json
 * @property string|null $msg_to_user
 * @property int $row_order
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereCloud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereMsgToUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereTokenJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserCloud whereUserId($value)
 * @mixin \Eloquent
 */
class UserCloud extends Model
{
    protected $table = 'user_clouds';

    public $timestamps = false;

    protected $hidden = [
        'token_json'
    ];

    protected $fillable = [
        'user_id',
        'name',
        'cloud',
        'msg_to_user',
        'token_json',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    /**
     * @return null|string
     */
    public function gettoken()
    {
        return $this->token_json ? TabldaEncrypter::decrypt($this->token_json) : null;
    }


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
