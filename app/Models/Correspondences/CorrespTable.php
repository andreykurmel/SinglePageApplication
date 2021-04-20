<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class CorrespTable extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'correspondence_tables';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'correspondence_app_id', //required
        'app_table', //required
        'data_table',
        'options',
        'active',
        'on_change_app_id',
        'notes'
    ];

    /**
     *  #model_hook
     */
    public static function boot()
    {
        parent::boot();
        CorrespTable::creating(function ($data) { CorrespTable::on_creating($data); });
    }

    public static function on_creating($data) {
        $data['active'] = $data['active'] ?? 0;
    }
    //-----------------------


    public function scopeIsActive($builder) {
        $builder->where('active', 1);
    }

    public function _app() {
        return $this->belongsTo(CorrespApp::class, 'correspondence_app_id', 'id');
    }

    public function _fields() {
        return $this->hasMany(CorrespField::class, 'correspondence_table_id', 'id');
    }
}
