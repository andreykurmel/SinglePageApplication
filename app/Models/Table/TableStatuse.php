<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableStatuse
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $status_data
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_user
 * @mixin \Eloquent
 */
class TableStatuse extends Model
{
    protected $table = 'table_statuses';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'status_data',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
