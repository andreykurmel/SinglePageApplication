<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableReferenceCorr
 *
 * @property int $id
 * @property int $import_ref_id
 * @property int $table_field_id
 * @property int $ref_field_id
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\Models\Table\TableField|null $_ref_field
 * @mixin \Eloquent
 */
class TableReferenceCorr extends Model
{
    protected $table = 'table_import_reference_corrs';

    public $timestamps = false;

    protected $fillable = [
        'import_ref_id',
        'table_field_id',
        'ref_field_id'
    ];



    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }

    public function _ref_field() {
        return $this->hasOne(TableField::class, 'id', 'ref_field_id');
    }
}
