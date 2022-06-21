<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\DDLReferenceColor
 *
 * @property int $id
 * @property int $ddl_reference_id
 * @property string $ref_value
 * @property string|null $color
 * @property-read \Vanguard\Models\DDLReference $_ddl_reference
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor whereDdlReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\DDLReferenceColor whereRefValue($value)
 * @mixin \Eloquent
 */
class DDLReferenceColor extends Model
{
    protected $table = 'ddl_reference_colors';

    public $timestamps = false;

    protected $fillable = [
        'ddl_reference_id',
        'ref_value',
        'color',
    ];


    public function _ddl_reference() {
        return $this->belongsTo(DDLReference::class, 'ddl_reference_id', 'id');
    }
}
