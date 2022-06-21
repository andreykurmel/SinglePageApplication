<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserInvitation
 *
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string|null $date_send
 * @property string|null $date_accept
 * @property string|null $date_confirm
 * @property int $status
 * @property int $rewarded
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereDateAccept($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereDateConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereDateSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereRewarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserInvitation whereUserId($value)
 * @mixin \Eloquent
 */
class UserInvitation extends Model
{
    protected $table = 'user_invitations';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'date_send',
        'date_accept',
        'date_confirm',
        'status' // 0 - created, 1 - sent, 2 - accepted
    ];


    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
