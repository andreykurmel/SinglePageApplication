<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Import
 *
 * @property int $id
 * @property string $file
 * @property int $complete
 * @property string $status
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereCreatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereModifiedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereModifiedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Import whereStatus($value)
 * @mixin \Eloquent
 */
class Import extends Model
{
    protected $table = 'imports';

    public $timestamps = false;

    protected $fillable = [
        'file',
        'complete',
        'status',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];
}
