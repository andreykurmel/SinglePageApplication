<?php

namespace Vanguard\Models\Dcr;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\User\UserGroup2TableDataRequest;
use Vanguard\User;

/**
 * Vanguard\Models\Dcr\TableDataRequestDefaultField
 *
 * @property int $id
 * @property int $table_data_requests_id
 * @property int $table_field_id
 * @property string $default
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\Models\Dcr\TableDataRequest $_table_data_request
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField whereTableDataRequestsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Dcr\TableDataRequestDefaultField whereTableFieldId($value)
 * @mixin \Eloquent
 */
class TableDataRequestDefaultField extends Model
{
    protected $table = 'table_data_requests_def_fields';

    public $timestamps = false;

    protected $fillable = [
        'table_data_requests_id',
        'table_field_id',
        'default',
    ];


    public function _table_data_request() {
        return $this->belongsTo(TableDataRequest::class, 'table_data_requests_id', 'id');
    }

    public function _field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
