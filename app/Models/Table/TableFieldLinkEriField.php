<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Vanguard\Models\Table\TableFieldLinkEriField
 *
 * @property int $id
 * @property int $table_link_eri_id
 * @property string $eri_variable
 * @property int $eri_field_id
 * @property int $eri_master_field_id
 * @property int $is_active
 * @property int $row_order
 * @property-read TableFieldLinkEriTable $_eri
 * @property-read Collection|TableFieldLinkEriFieldConversion[] $_conversions
 * @property-read TableField $_field
 * @property-read TableField $_master_field
 * @mixin Eloquent
 */
class TableFieldLinkEriField extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_eri_fields';

    protected $fillable = [
        'table_link_eri_id',
        'eri_variable',
        'eri_field_id',
        'eri_master_field_id',
        'is_active',
        'row_order',
    ];


    public function _eri()
    {
        return $this->belongsTo(TableFieldLinkEriTable::class, 'table_link_eri_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'eri_field_id', 'id');
    }

    public function _master_field()
    {
        return $this->belongsTo(TableField::class, 'eri_master_field_id', 'id');
    }

    public function _conversions()
    {
        return $this->hasMany(TableFieldLinkEriFieldConversion::class, 'table_link_eri_field_id', 'id');
    }
}
