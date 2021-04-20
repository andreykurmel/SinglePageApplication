<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class CorrespApp extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'correspondence_apps';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'is_active',
        'name', //required
        'host',
        'login',
        'pass',
        'db', //required
        'app_path', //required
        'iframe_app_path', //this or 'controller'
        'subdomain', //required
        'is_public',
        'notes',
        'code', //required
        'type', // ['local','iframe']
        'controller', //this or 'iframe_app_path'
        'open_as_popup',
    ];

    //get only active Apps
    public function scopeOnlyActive($query) {
        return $query->whereNotNull('subdomain')
            ->where('is_active', '=', 1);
    }
    public function scopeOnlyPublicActive($query) {
        return $query->whereNotNull('subdomain')
            ->where('is_public', '=', 1)
            ->where('is_active', '=', 1);
    }

    //get only Owned or Subscribed Apps.
    public function scopeOwnedOrSubscribed($query) {
        $user = auth()->user() ?: new User();
        $query->onlyActive();
        if (!$user->isAdmin()) {
            $query->where(function ($sub) use ($user) {
                $sub->orWhere('user_id', $user->id);
                $sub->orWhereIn('id', $user->_subscribed_apps->pluck('app_id'));
            });
        }
        return $query;
    }

    //relations
    public function _tables() {
        return $this->hasMany(CorrespTable::class, 'correspondence_app_id', 'id');
    }
}
