<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\FavoriteRow
 *
 * @property int $id
 * @property int $user_id
 * @property int $table_id
 * @property int $row_id
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\FavoriteRow whereUserId($value)
 * @mixin \Eloquent
 */
class FavoriteRow extends Model
{
    protected $table = 'favorite_rows';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'row_id',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
