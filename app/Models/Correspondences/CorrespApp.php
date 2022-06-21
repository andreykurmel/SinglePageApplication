<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

/**
 * Vanguard\Models\Correspondences\CorrespApp
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $is_active
 * @property string $name
 * @property string|null $host
 * @property string|null $login
 * @property string|null $pass
 * @property string $db
 * @property string|null $notes
 * @property string|null $app_path
 * @property string $iframe_app_path
 * @property string|null $subdomain
 * @property string|null $icon_full_path
 * @property string|null $row_hash
 * @property string|null $code
 * @property string|null $type
 * @property string|null $controller
 * @property int $open_as_popup
 * @property int $row_order
 * @property int $is_public
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Correspondences\CorrespTable[] $_tables
 * @property-read int|null $_tables_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp onlyActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp onlyPublicActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp ownedOrSubscribed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereAppPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereController($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereIconFullPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereIframeAppPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereOpenAsPopup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp wherePass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereSubdomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespApp whereUserId($value)
 * @mixin \Eloquent
 */
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
        return $this->hasMany(CorrespTable::class, 'correspondence_app_id', 'id')
            ->whereNotIn('row_hash', (new HelperService())->sys_row_hash);
    }
}
