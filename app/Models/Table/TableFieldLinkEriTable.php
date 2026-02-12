<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkEriTable
 *
 * @property int $id
 * @property int $table_link_id
 * @property int $eri_part_id
 * @property int $eri_table_id
 * @property int $is_active
 * @property int $row_order
 * @property-read TableFieldLink $_link
 * @property-read TableFieldLinkEriPart $_eri_part
 * @property-read Table $_eri_table
 * @property-read TableFieldLinkEriField[] $_eri_fields
 * @mixin Eloquent
 */
class TableFieldLinkEriTable extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_tables';

    protected $fillable = [
        'table_link_id',
        'eri_part_id',
        'eri_table_id',
        'is_active',
        'row_order',
    ];


    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'table_link_id', 'id');
    }

    public function _eri_part()
    {
        return $this->belongsTo(TableFieldLinkEriPart::class, 'eri_part_id', 'id');
    }

    public function _eri_table()
    {
        return $this->belongsTo(Table::class, 'eri_table_id', 'id');
    }

    public function _eri_fields() {
        return $this->hasMany(TableFieldLinkEriField::class, 'table_link_eri_id', 'id');
    }
}
