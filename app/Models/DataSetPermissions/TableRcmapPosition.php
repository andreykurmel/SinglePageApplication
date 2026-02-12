<?php

namespace Vanguard\Models\DataSetPermissions;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Helpers\ColorHelper;

/**
 * Vanguard\Models\DataSetPermissions\TableRcmapPosition
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $object_type
 * @property int $object_id
 * @property float $pos_x
 * @property float $pos_y
 * @property int $used_only
 * @property int $opened
 * @property int $changed
 * @property int $visible
 * @mixin Eloquent
 */
class TableRcmapPosition extends Model
{
    protected $table = 'table_rcmap_positions';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'object_type',
        'object_id',
        'pos_x',
        'pos_y',
        'used_only', //show/hide table fields unused in RCs
        'opened', //opened/closed table fields
        'changed',
        'visible',
    ];

    public function toArray()
    {
        $array = parent::toArray();
        $array['__ln_color'] = '#' . ColorHelper::autoHex(0, $this->id);
        return $array;
    }
}
