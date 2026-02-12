<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkEriField
 *
 * @property int $id
 * @property int $table_link_eri_field_id
 * @property string $eri_convers
 * @property string $tablda_convers
 * @property-read TableFieldLinkEriField $_eri_field
 * @mixin Eloquent
 */
class TableFieldLinkEriFieldConversion extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_field_conversions';

    protected $fillable = [
        'table_link_eri_field_id',
        'eri_convers',
        'tablda_convers',
    ];


    public function _eri_field()
    {
        return $this->belongsTo(TableFieldLinkEriField::class, 'table_link_eri_field_id', 'id');
    }
}
