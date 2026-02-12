<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\FormulaHelper
 *
 * @property int $id
 * @property string $formula
 * @property string|null $notes
 * @property string|null $row_hash
 * @property int|null $row_order
 * @mixin \Eloquent
 */
class FormulaHelper extends Model
{
    protected $table = 'formula_helpers';

    public $timestamps = false;

    protected $fillable = [
        'formula',
        'notes',
    ];
}
