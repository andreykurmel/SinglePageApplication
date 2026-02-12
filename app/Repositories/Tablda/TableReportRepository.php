<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableReport;
use Vanguard\Models\Table\TableReportRight;
use Vanguard\Models\Table\TableReportSource;
use Vanguard\Models\Table\TableReportSourceVariable;
use Vanguard\Models\Table\TableReportTemplate;
use Vanguard\Modules\Permissions\PermissionObject;
use Vanguard\Services\Tablda\HelperService;

class TableReportRepository
{
    protected $service;

    /**
     * TableAlertRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $table->load([
            '_report_templates',
            '_reports' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with([
                    '_report_rights',
                    '_sources' => function ($v) {
                        $v->with('_variables');
                    }
                ]);
            }
        ]);
        return $table;
    }

    /**
     * @param int $model_id
     * @return TableReport
     */
    public function get(int $model_id)
    {
        return TableReport::where('id', '=', $model_id)->first();
    }

    /**
     * @param int $model_id
     * @return TableReportSource
     */
    public function getSource(int $model_id)
    {
        return TableReportSource::where('id', '=', $model_id)->first();
    }

    /**
     * @param int $model_id
     * @return TableReportSourceVariable
     */
    public function getVariable(int $model_id)
    {
        return TableReportSourceVariable::where('id', '=', $model_id)->first();
    }

    /**
     * @param int $model_id
     * @return TableReportTemplate
     */
    public function getTemplate(int $model_id)
    {
        return TableReportTemplate::where('id', '=', $model_id)->first();
    }

    /**
     * @param Table $table
     * @param array $input
     * @return array
     */
    protected function reportDef(Table $table, array $input): array
    {
        return array_merge($input, [
            'table_id' => $table->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return Model|TableReport
     */
    public function insert(Table $table, array $data)
    {
        $data = $this->reportDef($table, $data);
        $report = TableReport::create($this->service->delSystemFields($data));
        $this->insertSource($report, ['name' => 'Self']);
        return $report;
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(Table $table, $model_id, array $data)
    {
        $data = $this->reportDef($table, $data);
        return TableReport::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($model_id)
    {
        return TableReport::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param Table $table
     * @param array $input
     * @return array
     */
    protected function reportTemplateDef(Table $table, array $input): array
    {
        $idx = strpos($input['template_file'] ?? '', '/edit');
        if ($idx !== false) {
            $input['template_file'] = substr($input['template_file'], 0, $idx);
        }
        return array_merge($input, [
            'table_id' => $table->id,
            'user_id' => auth()->id(),
            'static_hash' => Uuid::uuid4(),
        ]);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return TableReportTemplate
     */
    public function insertTemplate(Table $table, array $data)
    {
        $data = $this->reportTemplateDef($table, $data);
        return TableReportTemplate::create($this->service->delSystemFields($data));
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateTemplate(Table $table, $model_id, array $data)
    {
        $data = $this->reportTemplateDef($table, $data);
        return TableReportTemplate::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTemplate($model_id)
    {
        $reportTable = (new TableRepository())->getTableByDB('table_report_templates');
        (new FileRepository())->deleteFilesForRow($reportTable, [$model_id]);

        return TableReportTemplate::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param TableReport $report
     * @param array $data
     * @return Model|TableReportSource
     */
    public function insertSource(TableReport $report, array $data)
    {
        $data['table_report_id'] = $report->id;
        return TableReportSource::create($this->service->delSystemFields($data));
    }

    /**
     * @param TableReport $report
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateSource(TableReport $report, $model_id, array $data)
    {
        $data['table_report_id'] = $report->id;
        return TableReportSource::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteSource($model_id)
    {
        return TableReportSource::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param TableReportSource $source
     * @param array $data
     * @return Model|TableReportSourceVariable
     */
    public function insertSourceVariable(TableReportSource $source, array $data)
    {
        $data['table_report_source_id'] = $source->id;
        return TableReportSourceVariable::create($this->service->delSystemFields($data));
    }

    /**
     * @param TableReportSource $source
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateSourceVariable(TableReportSource $source, $model_id, array $data)
    {
        $data['table_report_source_id'] = $source->id;
        return TableReportSourceVariable::where('id', '=', $model_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteSourceVariable($model_id)
    {
        return TableReportSourceVariable::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param TableReport $report
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableReportRight
     */
    public function toggleReportRight(TableReport $report, int $table_permis_id, $can_edit)
    {
        $right = $report->_report_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableReportRight::create([
                'table_report_id' => $report->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableReport $report
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteReportRight(TableReport $report, int $table_permis_id)
    {
        return $report->_report_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}