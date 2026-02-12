<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableReport
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $name
 * @property array $filters_object
 * @property int|null $related_colgroup_id
 * @property Table $_table
 * @property User $_user
 * @mixin Eloquent
 */
class TableSavedFilter extends Model
{
    public $timestamps = false;

    protected $table = 'table_saved_filters';

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'filters_object',
        'related_colgroup_id',
    ];

    protected $casts = [
        'filters_object' => 'array'
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
