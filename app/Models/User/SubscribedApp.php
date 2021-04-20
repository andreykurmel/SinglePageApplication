<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class SubscribedApp extends Model
{
    protected $table = 'user_subscribed_apps';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'app_id',
    ];


    public function _available_features() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
