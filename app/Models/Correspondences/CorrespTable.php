<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\Correspondences\CorrespTable
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $correspondence_app_id
 * @property string|null $app_table
 * @property string|null $data_table
 * @property string|null $notes
 * @property string|null $row_hash
 * @property int $row_order
 * @property int $active
 * @property string|null $options
 * @property int|null $on_change_app_id
 * @property-read \Vanguard\Models\Correspondences\CorrespApp|null $_app
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Correspondences\CorrespField[] $_fields
 * @property-read int|null $_fields_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable isActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereAppTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereCorrespondenceAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereDataTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereOnChangeAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespTable whereUserId($value)
 * @mixin \Eloquent
 */
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
