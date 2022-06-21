<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\UnitConversion
 *
 * @property int $id
 * @property string $from_unit
 * @property string $to_unit
 * @property string $operator
 * @property float|null $factor
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property string|null $row_hash
 * @property string|null $property
 * @property int $row_order
 * @property string|null $from_unit_sys
 * @property string|null $formula
 * @property string|null $formula_reverse
 * @property string|null $formula_formula
 * @property string|null $formula_reverse_formula
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFormulaFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFormulaReverse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFormulaReverseFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFromUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereFromUnitSys($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereProperty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UnitConversion whereToUnit($value)
 * @mixin \Eloquent
 */
class UnitConversion extends Model
{
    protected $table = 'unit_conversion';

    public $timestamps = false;

    protected $fillable = [
        'property',
        'from_unit',
        'to_unit',
        'operator',
        'factor',
        'formula',
        'formula_reverse',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];
}
