<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserEmailAccount
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $email
 * @property string $app_pass
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount whereAppPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserEmailAccount whereUserId($value)
 * @mixin \Eloquent
 */
class UserEmailAccount extends Model
{
    protected $table = 'user_email_accounts';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'email',
        'app_pass',
    ];


    /**
     * @return string
     */
    public function decryptedKey()
    {
        return TabldaEncrypter::decrypt($this->app_pass ?? '');
    }


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
