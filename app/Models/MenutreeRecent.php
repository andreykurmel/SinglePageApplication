<?php

namespace Vanguard\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\MenutreeRecent
 *
 * @property int $id
 * @property int $user_id
 * @property string $object_type
 * @property int $object_id
 * @property string $last_access
 * @mixin Eloquent
 */
class MenutreeRecent extends Model
{
    protected $table = 'menutree_recent';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'object_type',
        'object_id',
        'last_access',
    ];

    public function _user()
    {
        $this->belongsTo(User::class);
    }
}
