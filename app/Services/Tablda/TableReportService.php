<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableReport;
use Vanguard\Models\Table\TableReportRight;
use Vanguard\Models\Table\TableReportSource;
use Vanguard\Models\Table\TableReportSourceVariable;
use Vanguard\Models\Table\TableReportTemplate;
use Vanguard\Modules\Report\ReportModule;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableReportRepository;
use Vanguard\Repositories\Tablda\TableRepository;

class TableReportService
{
    protected $addonRepo;
    protected $tableRepo;
    protected $dataRepo;
    protected $fileRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->addonRepo = new TableReportRepository();
        $this->tableRepo = new TableRepository();
        $this->dataRepo = new TableDataRowsRepository();
        $this->fileRepo = new FileRepository();
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
        $reportTable = $this->tableRepo->getTableByDB('table_report_templates');
        $this->dataRepo->attachSpecialFields($table->_report_templates, $reportTable, auth()->id(), ['files']);
        return $table;
    }

    /**
     * @param int $model_id
     * @return TableReport
     */
    public function get(int $model_id)
    {
        return $this->addonRepo->get($model_id);
    }

    /**
     * @param int $model_id
     * @return TableReportSource
     */
    public function getSource(int $model_id)
    {
        return $this->addonRepo->getSource($model_id);
    }

    /**
     * @param int $model_id
     * @return TableReportSourceVariable
     */
    public function getVariable(int $model_id)
    {
        return $this->addonRepo->getVariable($model_id);
    }

    /**
     * @param int $model_id
     * @return TableReportTemplate
     */
    public function getTemplate(int $model_id)
    {
        return $this->addonRepo->getTemplate($model_id);
    }

    /**
     * @param int $report_id
     * @param array $request_params
     * @param int|null $direct_row_id
     * @return string
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function makeReports(int $report_id, array $request_params, int $direct_row_id = null): string
    {
        $report = $this->get($report_id);
        if ($direct_row_id) {
            $reportedRows = [
                (new TableDataService())->getDirectRow($report->_table, $direct_row_id)
            ];
        } else {
            $reportedRows = $this->dataRepo->getOnlyRows(
                $report->_table,
                $request_params,
                auth()->id(),
                [
                    'row_group_id' => $request_params['selected_row_group_id'] ?? null,
                    'saved_filter_id' => $request_params['selected_saved_filter_id'] ?? null,
                ]
            );
            $reportedRows = $reportedRows['rows'];
        }
        $module = new ReportModule($report);
        foreach ($reportedRows as $row) {
            $module->makeForRow($row->toArray());
        }
        return 'done';
    }

    /**
     * @param int $report_id
     * @return array
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function templateVariables(int $report_id): array
    {
        $report = $this->get($report_id);
        return (new ReportModule($report))->availVariables();
    }

    /**
     * @param Table $table
     * @param array $data
     * @return Model|TableReport
     */
    public function insert(Table $table, array $data)
    {
        return $this->addonRepo->insert($table, $data);
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(Table $table, $model_id, array $data)
    {
        return $this->addonRepo->update($table, $model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($model_id)
    {
        return $this->addonRepo->delete($model_id);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return TableReportTemplate
     */
    public function insertTemplate(Table $table, array $data)
    {
        $report = $this->addonRepo->insertTemplate($table, $data);
        if (!empty($data['_temp_id'])) {
            $reportTable = $this->tableRepo->getTableByDB('table_report_templates');
            $this->fileRepo->storeTempFiles($reportTable, $data['_temp_id'], $report->id);
        }
        return $report;
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateTemplate(Table $table, $model_id, array $data)
    {
        return $this->addonRepo->updateTemplate($table, $model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTemplate($model_id)
    {
        return $this->addonRepo->deleteTemplate($model_id);
    }

    /**
     * @param TableReport $report
     * @param array $data
     * @return Model|TableReportSource
     */
    public function insertSource(TableReport $report, array $data)
    {
        return $this->addonRepo->insertSource($report, $data);
    }

    /**
     * @param TableReport $report
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateSource(TableReport $report, $model_id, array $data)
    {
        return $this->addonRepo->updateSource($report, $model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteSource($model_id)
    {
        return $this->addonRepo->deleteSource($model_id);
    }

    /**
     * @param TableReportSource $source
     * @param array $data
     * @return Model|TableReportSourceVariable
     */
    public function insertSourceVariable(TableReportSource $source, array $data)
    {
        return $this->addonRepo->insertSourceVariable($source, $data);
    }

    /**
     * @param TableReportSource $source
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function updateSourceVariable(TableReportSource $source, $model_id, array $data)
    {
        return $this->addonRepo->updateSourceVariable($source, $model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteSourceVariable($model_id)
    {
        return $this->addonRepo->deleteSourceVariable($model_id);
    }

    /**
     * @param TableReport $report
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableReportRight
     */
    public function toggleReportRight(TableReport $report, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleReportRight($report, $table_permis_id, $can_edit);
    }

    /**
     * @param TableReport $report
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteReportRight(TableReport $report, int $table_permis_id)
    {
        return $this->addonRepo->deleteReportRight($report, $table_permis_id);
    }
}