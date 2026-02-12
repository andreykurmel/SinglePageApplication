<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableTournament
 *
 * @property int $id
 * @property int $grouping_field_id
 * @property int $field_id
 * @property string|null $stat_fn
 * @property TableGroupingField $_grouping_field
 * @mixin Eloquent
 */
class TableGroupingFieldStat extends Model
{
    public $timestamps = false;

    protected $table = 'table_grouping_field_stats';

    protected $fillable = [
        'grouping_field_id',
        'field_id',
        'stat_fn',
    ];


    public function _grouping_field()
    {
        return $this->belongsTo(TableGroupingField::class, 'grouping_field_id', 'id');
    }
}
