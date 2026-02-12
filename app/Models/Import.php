<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Import
 *
 * @property int $id
 * @property int $table_id
 * @property string $file
 * @property int $complete
 * @property string $status
 * @property string $type
 * @property string $date
 * @property string $info
 * @mixin \Eloquent
 */
class Import extends Model
{
    protected $table = 'imports';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'file',
        'complete',
        'status',
        'type',
        'date',//current timestamp
        'info',
    ];
}
