<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

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
