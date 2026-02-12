<?php

namespace Vanguard\Models\Dcr;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\Table\TableFieldLink;

/**
 * DcrNotifLinkedTable
 *
 * @property int $id
 * @property int $alert_id
 * @property int $link_id
 * @property int $is_active
 * @property string|null $related_format
 * @property int|null $related_col_group_id
 * @property string $type
 * @property string|null $description
 * @property-read TableDataRequest $_dcr
 * @property-read TableFieldLink $_link
 * @property-read TableColumnGroup|null $_col_group
 * @mixin Eloquent
 */
class DcrNotifLinkedTable extends Model
{
    protected $table = 'dcr_notif_linked_tables';

    public $timestamps = false;

    protected $fillable = [
        'dcr_id',
        'link_id',
        'related_format',
        'related_col_group_id',
        'is_active',
        'type',//'save','update','submit'
        'description',
    ];


    public function _dcr()
    {
        return $this->belongsTo(TableDataRequest::class, 'dcr_id', 'id');
    }

    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'link_id', 'id');
    }

    public function _col_group() {
        return $this->hasOne(TableColumnGroup::class, 'id', 'related_col_group_id');
    }
}
