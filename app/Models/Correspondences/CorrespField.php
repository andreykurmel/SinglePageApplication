<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class CorrespField extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'correspondence_fields';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'correspondence_app_id', //required
        'correspondence_table_id', //required
        'app_field', //required
        'data_field',
        'link_table_db',
        'link_field_db',
        'link_field_type',
        'options',
        'notes'
    ];

    public function _app() {
        return $this->belongsTo(CorrespApp::class, 'correspondence_app_id', 'id');
    }

    public function _table() {
        return $this->belongsTo(CorrespTable::class, 'correspondence_table_id', 'id');
    }
}
