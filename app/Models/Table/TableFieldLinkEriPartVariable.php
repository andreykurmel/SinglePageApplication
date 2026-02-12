<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkEriPartVariable
 *
 * @property int $id
 * @property int $table_link_eri_part_id
 * @property string $variable_name
 * @property string $var_notes
 * @property int $row_order
 * @property-read TableFieldLinkEriTable $_eri
 * @mixin Eloquent
 */
class TableFieldLinkEriPartVariable extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_part_variables';

    protected $fillable = [
        'table_link_eri_part_id',
        'variable_name',
        'var_notes',
        'row_order',
    ];


    public function _part()
    {
        return $this->belongsTo(TableFieldLinkEriPart::class, 'table_link_eri_part_id', 'id');
    }
}
