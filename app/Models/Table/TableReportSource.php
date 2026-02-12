<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;

/**
 * Vanguard\Models\Table\TableReportSource
 *
 * @property int $id
 * @property int $table_report_id
 * @property string $name
 * @property int|null $ref_link_id
 * @property string|null $description
 * @property TableFieldLink|null $_ref_link
 * @property Collection|TableReportSourceVariable[] $_variables
 * @mixin Eloquent
 */
class TableReportSource extends Model
{
    public $timestamps = false;

    protected $table = 'table_report_sources';

    protected $fillable = [
        'table_report_id',
        'name',
        'ref_link_id',
        'description',
    ];


    public function _report()
    {
        return $this->belongsTo(TableReport::class, 'table_report_id', 'id');
    }

    public function _ref_link()
    {
        return $this->belongsTo(TableFieldLink::class, 'ref_link_id', 'id');
    }

    public function _variables()
    {
        return $this->hasMany(TableReportSourceVariable::class, 'table_report_source_id', 'id');
    }
}
