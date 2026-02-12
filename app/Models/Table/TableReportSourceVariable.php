<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableReportSourceVariable
 *
 * @property int $id
 * @property int $table_report_source_id
 * @property string $variable
 * @property string $type
 * @property int|null $object_id
 * @property string|null $additional_attributes
 * @mixin Eloquent
 */
class TableReportSourceVariable extends Model
{
    public $timestamps = false;

    protected $table = 'table_report_source_variables';

    public const TYPE_FIELD = 'field';
    public const TYPE_ROWS = 'rows';
    public const TYPE_BI = 'bi';

    protected $fillable = [
        'table_report_source_id',
        'variable',
        'variable_type',//['field','rows','bi']
        'var_object_id',
        'additional_attributes',
    ];


    public function _report_source()
    {
        return $this->belongsTo(TableReportSource::class, 'table_report_source_id', 'id');
    }
}
