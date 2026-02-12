<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableFieldLinkDaLoading
 *
 * @property int $id
 * @property int $table_link_id
 * @property string|null $column_key
 * @property int|null $da_field_id
 * @property int|null $da_master_field_id
 * @property int $is_active
 * @property int $row_order
 * @property-read TableFieldLink $_link
 * @property-read TableField $_field
 * @property-read TableField $_master_field
 * @mixin Eloquent
 */
class TableFieldLinkDaLoading extends Model
{
    public $timestamps = false;

    protected $table = 'table_field_link_da_loadings';

    protected $fillable = [
        'table_link_id',
        'column_key',
        'da_field_id',
        'da_master_field_id',
        'is_active',
        'row_order',
    ];


    public function _link()
    {
        return $this->belongsTo(TableFieldLink::class, 'table_link_id', 'id');
    }

    public function _field()
    {
        return $this->belongsTo(TableField::class, 'da_field_id', 'id');
    }

    public function _master_field()
    {
        return $this->belongsTo(TableField::class, 'da_master_field_id', 'id');
    }
}
