<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableTournament
 *
 * @property int $id
 * @property int $grouping_id
 * @property int $field_id
 * @property string|null $stat_fn
 * @property TableGroupingField $_grouping
 * @mixin Eloquent
 */
class TableGroupingStat extends Model
{
    public $timestamps = false;

    protected $table = 'table_grouping_stats';

    protected $fillable = [
        'grouping_id',
        'field_id',
        'stat_fn',
    ];


    public function _grouping()
    {
        return $this->belongsTo(TableGrouping::class, 'grouping_id', 'id');
    }
}
