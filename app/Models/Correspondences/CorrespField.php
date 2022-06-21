<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\Correspondences\CorrespField
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $correspondence_app_id
 * @property int|null $correspondence_table_id
 * @property string|null $app_field
 * @property string|null $data_field
 * @property string|null $notes
 * @property string|null $row_hash
 * @property string|null $options
 * @property int $row_order
 * @property string|null $link_table_db
 * @property string|null $link_field_db
 * @property string|null $link_field_type
 * @property-read \Vanguard\Models\Correspondences\CorrespApp|null $_app
 * @property-read \Vanguard\Models\Correspondences\CorrespTable|null $_table
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereAppField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereCorrespondenceAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereCorrespondenceTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereDataField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereLinkFieldDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereLinkFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereLinkTableDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespField whereUserId($value)
 * @mixin \Eloquent
 */
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
