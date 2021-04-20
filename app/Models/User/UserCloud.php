<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\User;

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
        'root_folder',
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
