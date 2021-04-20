<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

class TableUsedCode extends Model
{
    public $timestamps = false;

    protected $table = 'used_tmp_codes';

    protected $fillable = [
        'code',
    ];
}
