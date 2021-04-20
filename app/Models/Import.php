<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

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
