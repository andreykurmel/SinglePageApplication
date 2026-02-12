<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkEriPart
 *
 * @property int $id
 * @property int $link_id
 * @property int $eri_part_id
 * @property int $row_id
 * @property int $parser_active
 * @property int $writer_active
 * @property-read TableFieldLink $_link
 * @property-read TableFieldLinkEriPart[] $_eri_part
 * @mixin Eloquent
 */
class TableFieldLinkEriPartActive extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_part_actives';

    protected $fillable = [
        'link_id',
        'eri_part_id',
        'row_id',
        'parser_active',
        'writer_active',
    ];


    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'link_id', 'id');
    }

    public function _eri_part()
    {
        return $this->belongsTo(TableFieldLinkEriPart::class, 'eri_part_id', 'id');
    }
}
