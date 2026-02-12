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
 * @property int $field_name_visible
 * @property string|null $sorting
 * @property string|null $color
 * @property int $indent
 * @property string $default_state
 * @property int $rg_active
 * @property int $row_order
 * @property TableGrouping $_grouping
 * @property TableField $_field
 * @property TableGroupingFieldStat[] $_field_stats
 * @mixin Eloquent
 */
class TableGroupingField extends Model
{
    public $timestamps = false;

    protected $table = 'table_grouping_fields';

    protected $fillable = [
        'grouping_id',
        'field_id',
        'field_name_visible',
        'sorting',
        'color',
        'indent',
        'default_state',
        'rg_active',
        'row_order',
    ];


    public function _grouping()
    {
        return $this->belongsTo(TableGrouping::class, 'grouping_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'field_id', 'id');
    }

    public function _field_stats()
    {
        return $this->hasMany(TableGroupingFieldStat::class, 'grouping_field_id', 'id');
    }
}
