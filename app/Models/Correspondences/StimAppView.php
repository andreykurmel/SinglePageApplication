<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class StimAppView extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'stim_app_views';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'v_tab',
        'v_select',
        'source_string',
        'master_row_id',
        'name',
        'hash',
        'is_active',
        'is_locked',
        'lock_pass',
        'side_top',
        'side_left',
        'side_right',
    ];
}
