<?php

namespace Vanguard\Models\DataSetPermissions;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DataSetPermissions\TableRowGroupRegular
 *
 * @property int $id
 * @property int $table_row_group_id
 * @property string $field_value
 * @property string $list_field
 * @property string|null $row_json
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup $_row_group
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular whereFieldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular whereListField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular whereRowJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DataSetPermissions\TableRowGroupRegular whereTableRowGroupId($value)
 * @mixin \Eloquent
 */
class TableRowGroupRegular extends Model
{
    protected $table = 'table_row_group_regulars';

    public $timestamps = false;

    protected $fillable = [
        'table_row_group_id',
        'list_field',
        'field_value',
        'row_json'
    ];


    public function _row_group() {
        return $this->belongsTo(TableRowGroup::class, 'table_row_group_id', 'id');
    }
}
