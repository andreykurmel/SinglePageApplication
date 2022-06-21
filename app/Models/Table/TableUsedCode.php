<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableUsedCode
 *
 * @property int $id
 * @property string $code
 * @mixin \Eloquent
 */
class TableUsedCode extends Model
{
    public $timestamps = false;

    protected $table = 'used_tmp_codes';

    protected $fillable = [
        'code',
    ];
}
