<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;

class CorrespStim3D extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'correspondence_stim_3d';

    public $timestamps = false;

    protected $fillable = [
        'top_tab',
        'select',
        'style', // ['vh_tabs', 'accordion']
        'accordion',
        'horizontal',
        'vertical',
        'type_tablda',
        'db_table',
        'options',
        'model_3d',
    ];
}
