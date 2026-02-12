<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkParam
 *
 * @property int $id
 * @property int $table_link_id
 * @property int $field_id
 * @property int $in_popup_display
 * @property int $in_inline_display
 * @property-read TableFieldLink $_link
 * @property-read TableField $_field
 * @mixin Eloquent
 */
class TableFieldLinkColumn extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_columns';

    protected $fillable = [
        'table_link_id',
        'field_id',
        'field_db',
        'in_popup_display',
        'in_inline_display',
    ];


    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'table_link_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'field_id', 'id');
    }
}
