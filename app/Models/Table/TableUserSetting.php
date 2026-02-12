<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableUserSetting
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property int $initial_view_id
 * @property int $user_fld_show_image
 * @property int $user_fld_show_first
 * @property int $user_fld_show_last
 * @property int $user_fld_show_email
 * @property int $history_user_show_image
 * @property int $history_user_show_first
 * @property int $history_user_show_last
 * @property int $history_user_show_email
 * @property int $user_fld_show_username
 * @property int $history_user_show_username
 * @property int $vote_user_show_image
 * @property int $vote_user_show_first
 * @property int $vote_user_show_last
 * @property int $vote_user_show_email
 * @property int $vote_user_show_username
 * @property int $max_cell_rows
 * @property int $cell_height
 * @property int $left_menu_width
 * @property int $right_menu_width
 * @property int $stim_filter_width
 * @property-read \Vanguard\Models\Table\TableView|null $_initial_view
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_user
 * @mixin \Eloquent
 */
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

        'max_cell_rows',
        'cell_height',
        'left_menu_width',
        'right_menu_width',
        'stim_filter_width',
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
    public function getUserFilterStr(User $user)
    {
        if (!$user) {
            return '';
        }

        $res = [];
        $res[] = '<img src="'.$user->avatarLink().'">';

        if ($user->first_name) {
            $res[] = $user->first_name;
        }
        if ($user->last_name) {
            $res[] = $user->last_name;
        }
        /*if ($user->email) {
            $res[] = $user->email ? '('.$user->email.')' : '';
        }*/
        if (count($res) <= 1) {
            $res[] = $user->username;
        }

        return implode(' ', $res);
    }
}
