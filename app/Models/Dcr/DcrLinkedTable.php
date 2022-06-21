<?php

namespace Vanguard\Models\Dcr;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;

/**
 * Vanguard\Models\Dcr\DcrLinkedTable
 *
 * @property int $id
 * @property int $is_active
 * @property int $table_request_id
 * @property int $linked_table_id
 * @property int|null $linked_permission_id
 * @property int|null $position_field_id
 * @property int|null $passed_ref_cond_id
 * @property string|null $header
 * @property string|null $position
 * @property string|null $style
 * @property-read TablePermission|null $_linked_permission
 * @property-read Table $_linked_table
 * @property-read TableRefCondition|null $_passed_ref_cond
 * @property-read TableField|null $_position_field
 * @property-read TableDataRequest $_table_data_request
 * @mixin Eloquent
 */
class DcrLinkedTable extends Model
{
    public $timestamps = false;

    protected $table = 'dcr_linked_tables';

    protected $fillable = [
        'is_active',
        'table_request_id',
        'linked_table_id',
        'linked_permission_id',
        'position_field_id',
        'passed_ref_cond_id',
        'header',
        'position',
        'style',//['Default','Top/Bot']
    ];


    public function _table_data_request()
    {
        return $this->belongsTo(TableDataRequest::class, 'table_request_id', 'id');
    }

    public function _linked_table()
    {
        return $this->belongsTo(Table::class, 'linked_table_id', 'id');
    }

    public function _linked_permission()
    {
        return $this->belongsTo(TablePermission::class, 'linked_permission_id', 'id');
    }

    public function _passed_ref_cond()
    {
        return $this->belongsTo(TableRefCondition::class, 'passed_ref_cond_id', 'id');
    }

    public function _position_field()
    {
        return $this->belongsTo(TableField::class, 'position_field_id', 'id');
    }
}
