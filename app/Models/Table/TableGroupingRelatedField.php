<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $grouping_id
 * @property int $field_id
 * @property int $fld_visible
 * @property int $fld_order
 * @property TableGrouping $_grouping
 * @property TableField $_field
 * @mixin Eloquent
 */
class TableGroupingRelatedField extends Model
{
    public $timestamps = false;

    protected $table = 'table_grouping_related_fields';

    protected $fillable = [
        'grouping_id',
        'field_id',
        'fld_visible',
        'fld_order',
    ];


    public function _grouping()
    {
        return $this->belongsTo(TableGrouping::class, 'grouping_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'field_id', 'id');
    }
}
