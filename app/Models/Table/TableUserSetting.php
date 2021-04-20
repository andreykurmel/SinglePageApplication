<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class TableUserSetting extends Model
{
    protected $table = 'table_user_settings';

    public $timestamps = false;

    public static $user_show_fields = [
        'user_fld_show_image',
        'user_fld_show_first',
        'user_fld_show_last',
        'user_fld_show_email',
        'user_fld_show_username',

        'history_user_show_image',
        'history_user_show_first',
        'history_user_show_last',
        'history_user_show_email',
        'history_user_show_username',

        'vote_user_show_image',
        'vote_user_show_first',
        'vote_user_show_last',
        'vote_user_show_email',
        'vote_user_show_username',
    ];

    protected $fillable = [
        'table_id',
        'user_id',
        'initial_view_id',

        'user_fld_show_image',
        'user_fld_show_first',
        'user_fld_show_last',
        'user_fld_show_email',
        'user_fld_show_username',

        'history_user_show_image',
        'history_user_show_first',
        'history_user_show_last',
        'history_user_show_email',
        'history_user_show_username',

        'vote_user_show_image',
        'vote_user_show_first',
        'vote_user_show_last',
        'vote_user_show_email',
        'vote_user_show_username',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _initial_view() {
        return $this->hasOne(TableView::class, 'id', 'initial_view_id');
    }

    /**
     * @param User $user
     * @return string
     */
    public function getUserStr(User $user)
    {
        if (!$user) {
            return '';
        }

        $res = [];
        $res[] = $this->user_fld_show_image
        ? '<img src="'.$user->avatarLink().'">'
        : '';

        if ($this->user_fld_show_first) {
            $res[] = $user->first_name;
        }
        if ($this->user_fld_show_last) {
            $res[] = $user->last_name;
        }
        if ($this->user_fld_show_email) {
            $res[] = $user->email ? '('.$user->email.')' : '';
        }
        if ($this->user_fld_show_username) {
            $res[] = $user->username;
        }

        return implode(' ', $res);
    }
}
