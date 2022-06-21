<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableRowGroup;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableReference
 *
 * @property int $id
 * @property string|null $name
 * @property int $table_id
 * @property int $ref_table_id
 * @property int|null $ref_row_group_id
 * @property-read \Vanguard\Models\Table\Table|null $_ref_table
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\TableReferenceCorr[] $_reference_corrs
 * @property-read int|null $_reference_corrs_count
 * @property-read \Vanguard\Models\DataSetPermissions\TableRowGroup $_row_group
 * @property-read \Vanguard\Models\Table\Table $_table
 * @mixin \Eloquent
 */
class TableReference extends Model
{
    protected $table = 'table_import_references';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'table_id',
        'ref_table_id',
        'ref_row_group_id'
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _row_group() {
        return $this->belongsTo(TableRowGroup::class, 'table_row_group_id', 'id');
    }

    public function _ref_table() {
        return $this->hasOne(Table::class, 'id', 'ref_table_id');
    }

    public function _reference_corrs() {
        return $this->hasMany(TableReferenceCorr::class, 'import_ref_id', 'id');
    }
}
