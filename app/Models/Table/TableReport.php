<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableReport
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $report_name
 * @property string $report_data_range
 * @property string $report_template_id
 * @property string $report_file_formula
 * @property int $report_field_id
 * @property TableField|null $_report_field
 * @property TableReportTemplate $_template
 * @property Table $_table
 * @property Collection|TableReportRight[] $_report_rights
 * @property Collection|TableReportSource[] $_sources
 * @mixin Eloquent
 */
class TableReport extends Model
{
    public $timestamps = false;

    protected $table = 'table_reports';

    protected $fillable = [
        'table_id',
        'user_id',
        'report_name',
        'report_data_range',
        'report_template_id',
        'report_file_formula',
        'report_field_id',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions() {
        return $this->belongsToMany(TablePermission::class, 'table_report_rights', 'table_report_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_report_id', 'table_permission_id', 'can_edit']);
    }
    public function _report_rights() {
        return $this->hasMany(TableReportRight::class, 'table_report_id', 'id');
    }

    public function _user()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _template()
    {
        return $this->belongsTo(TableReportTemplate::class, 'report_template_id', 'id');
    }

    public function _report_field()
    {
        return $this->belongsTo(TableField::class, 'report_field_id', 'id');
    }

    public function _sources()
    {
        return $this->hasMany(TableReportSource::class, 'table_report_id', 'id');
    }
}
