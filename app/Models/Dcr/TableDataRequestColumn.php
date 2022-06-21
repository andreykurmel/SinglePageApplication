<?php

namespace Vanguard\Models\Dcr;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;

/**
 * Vanguard\Models\Dcr\TableDataRequestColumn
 *
 * @property int $id
 * @property int $table_data_requests_id
 * @property int $table_column_group_id
 * @property int $view
 * @property int $edit
 * @property-read \Vanguard\Models\DataSetPermissions\TableColumnGroup $_column_group
 * @property-read \Vanguard\Models\Dcr\TableDataRequest $_table_data_request
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn whereEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn whereTableColumnGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn whereTableDataRequestsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestColumn whereView($value)
 * @mixin \Eloquent
 */
class TableDataRequestColumn extends Model
{
    protected $table = 'table_data_requests_2_table_column_groups';

    public $timestamps = false;

    protected $fillable = [
        'table_data_requests_id',
        'table_column_group_id',
        'view',
        'edit',
    ];


    public function _table_data_request() {
        return $this->belongsTo(TableDataRequest::class, 'table_data_requests_id', 'id');
    }

    public function _column_group() {
        return $this->belongsTo(TableColumnGroup::class, 'table_column_group_id', 'id');
    }
}
