<?php

namespace Vanguard\Models\User;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserGroupCondition
 *
 * @property int $id
 * @property int $user_group_id
 * @property string|null $logic_operator
 * @property string $user_field
 * @property string $compared_operator
 * @property string $compared_value
 * @property-read \Vanguard\User $_created_user
 * @property-read \Vanguard\User $_modified_user
 * @property-read \Vanguard\Models\User\UserGroup $_user_group
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereComparedOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereComparedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereLogicOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereUserField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserGroupCondition whereUserGroupId($value)
 * @mixin \Eloquent
 */
class UserGroupCondition extends Model
{
    protected $table = 'user_group_conditions';

    public $timestamps = false;

    protected $fillable = [
        'user_group_id',
        'logic_operator',
        'user_field',
        'compared_operator',
        'compared_value',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _user_group() {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
