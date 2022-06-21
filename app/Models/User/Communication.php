<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\User\Communication
 *
 * @property int $id
 * @property int $table_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property string $date
 * @property string $message
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property int|null $to_user_group_id
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User $_from_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_to_user
 * @property-read \Vanguard\Models\User\UserGroup|null $_to_user_group
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereToUserGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\Communication whereToUserId($value)
 * @mixin \Eloquent
 */
class Communication extends Model
{
    protected $table = 'table_communications';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'from_user_id',
        'to_user_id',
        'to_user_group_id',
        'date',
        'message',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _from_user() {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function _to_user() {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }

    public function _to_user_group() {
        return $this->belongsTo(UserGroup::class, 'to_user_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
