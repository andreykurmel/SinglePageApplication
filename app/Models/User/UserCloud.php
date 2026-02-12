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
 * @property array|null $extra_params
 * @property int $row_order
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\User $_user
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
        'extra_params',
    ];

    protected $casts = [
        'extra_params' => 'array',
    ];

    public function jiraCloudId(): string
    {
        return $this->cloud == 'Jira' && !empty($this->extra_params['id'])
                ? (string) $this->extra_params['id']
                : '';
    }

    public function jiraDomain(): string
    {
        return $this->cloud == 'Jira' && !empty($this->extra_params['url'])
                ? (string) $this->extra_params['url']
                : '';
    }

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
