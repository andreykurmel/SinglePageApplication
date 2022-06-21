<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableFieldLinkParam
 *
 * @property int $id
 * @property int $table_field_link_id
 * @property string $param
 * @property string|null $value
 * @property int|null $column_id
 * @property-read \Vanguard\Models\Table\TableFieldLink $_field
 * @mixin \Eloquent
 */
class TableFieldLinkParam extends Model
{
    protected $table = 'table_field_link_params';

    public $timestamps = false;

    protected $fillable = [
        'table_field_link_id',
        'param',
        'value',
        'column_id',
    ];


    public function _field() {
        return $this->belongsTo(TableFieldLink::class, 'table_field_link_id', 'id');
    }
}
