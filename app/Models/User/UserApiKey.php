<?php

namespace Vanguard\Models\User;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserApiKey
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $key
 * @property string|null $air_base
 * @property string|null $air_type
 * @property int $is_active
 * @property-read User $_user
 * @mixin Eloquent
 * @property string $name
 * @property string $notes
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereAirBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereAirType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereUserId($value)
 */
class UserApiKey extends Model
{
    public $timestamps = false;
    protected $table = 'user_api_keys';
    protected $fillable = [
        'user_id',
        'name',
        'type', // 'google','sendgrid','extracttable','airtable'
        'key',
        'air_base', // used in key
        'air_type',
        'notes',
        'is_active',
    ];


    /**
     * @return string
     */
    public function decryptedKey()
    {
        return TabldaEncrypter::decrypt($this->key ?? '');
    }


    /**
     * @return BelongsTo
     */
    public function _user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
