<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkEriPart
 *
 * @property int $id
 * @property int $table_link_id
 * @property string $part
 * @property string $type
 * @property string $section_q_identifier
 * @property string $section_r_identifier
 * @property int $row_order
 * @property-read TableFieldLink $_link
 * @property-read TableFieldLinkEriPartVariable[] $_part_variables
 * @mixin Eloquent
 */
class TableFieldLinkEriPart extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_parts';

    protected $fillable = [
        'table_link_id',
        'part',
        'type',
        'section_q_identifier',
        'section_r_identifier',
        'row_order',
    ];


    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'table_link_id', 'id');
    }

    public function _part_variables() {
        return $this->hasMany(TableFieldLinkEriPartVariable::class, 'table_link_eri_part_id', 'id');
    }
}
