<?php

namespace Vanguard\Modules\Report;

use PhpOffice\PhpWord\Element\Table as OfficeTable;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\Settings;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableReport;
use Vanguard\Models\Table\TableReportSource;
use Vanguard\Models\Table\TableReportSourceVariable;
use Vanguard\Models\Table\TableReportTemplate;
use Vanguard\Modules\RemoteFilesModule;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\RemoteFilesRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Support\FileHelper;

class ReportModule
{
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var TableReport
     */
    protected $report;
    /**
     * @var TableReportTemplate
     */
    protected $templateModel;
    /**
     * @var string
     */
    protected $templatePath;
    /**
     * @var Table
     */
    protected $reportTable;
    /**
     * @var TableField
     */
    protected $remoteField;
    /**
     * @var RemoteFilesModule
     */
    protected $remoteFilesModule;

    /**
     * @param TableReport $report
     * @throws \Exception
     */
    public function __construct(TableReport $report)
    {
        Settings::setTempDir(storage_path('tmp'));
        $this->report = $report;
        $this->templateModel = $report->_template;
        if (!$this->templateModel) {
            throw new \Exception('Template is not found for the report '.$report->report_name.'!', 1);
        }
        $this->table = $report->_table;

        $this->reportTable = (new TableRepository())->getTableByDB('table_report_templates');
        $this->remoteField = $this->reportTable->_fields->where('field', '=', 'template_file')->first();
        $this->remoteFilesModule = new RemoteFilesModule($this->reportTable->id, $this->reportTable->_user, $this->docExt('docx'));

        $this->templatePath = $this->getTemplatePath();
        $ext = strtolower(FileHelper::extension($this->templatePath));
        if ($ext && $ext != $this->docExt('docx')) {
            throw new \Exception('Doc Type and File Extension are not the same!', 1);
        }
    }

    /**
     * @param string $default
     * @return string
     */
    protected function docExt(string $default = ''): string
    {
        switch ($this->templateModel->doc_type) {
            case 'data': $ext = 'data'; break;
            case 'txt': $ext = 'txt'; break;
            case 'ms_word': $ext = 'docx'; break;
            default: $ext = $default; break;
        }
        return $ext;
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        if ($this->templateModel->connected_cloud_id && $this->templateModel->template_source == 'URL') {
            $repo = new RemoteFilesRepository();
            $marker = $this->templateModel->connected_cloud_id . ':' . FileHelper::name($this->templateModel->template_file);

            $file = $repo->getSql($this->reportTable->id, null, $this->templateModel->id)->first();
            if (!$file || $file->notes != $marker) {
                $this->remoteFilesModule->remoteFilesCreation($this->remoteField, $this->templateModel->id, $this->templateModel->template_file);
                $file = $repo->getSql($this->reportTable->id, null, $this->templateModel->id)->first();
            }

            $file->update(['notes' => $marker]);
            return $this->remoteFilesModule->thumbPath($file);
        } else {
            $repo = new FileRepository();

            $file = $repo->getSql($this->reportTable->id, null, [$this->templateModel->id])->first();
            $filePath = $file ? $file->filepath . $file->filename : $this->templateModel->template_file;

            return storage_path('app/public/' . $filePath);
        }
    }

    /**
     * @param array $row
     * @return void
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     * @throws Exception
     */
    public function makeForRow(array $row): void
    {
        //replace variables in the template
        $fileContent = $this->prepareTemplateContent($row);

        //remove old reports
        $fileRepo = new FileRepository();
        $remoteRepo = new RemoteFilesRepository();
        $fileRepo->deleteFilesForRow($this->table, [$row['id']], $this->report->_report_field->field);
        $remoteRepo->delete($this->table->id, $this->report->report_field_id, $row['id']);

        //store locally
        $file = $fileRepo->insertFileAlias(
            $this->table->id,
            $this->report->report_field_id,
            $row['id'],
            $this->reportFilename($row),
            $fileContent
        );
        //move to cloud if needed
        if ($this->templateModel->connected_cloud_id && $this->templateModel->cloud_report_folder) {
            $this->remoteFilesModule->fileToSharedFolder(
                $this->report->_report_field,
                $file,
                $this->templateModel->cloud_report_folder,
                $fileContent
            );
            $file->delete();
        }
    }

    /**
     * @param array $row
     * @return string
     * @throws Exception
     */
    protected function prepareTemplateContent(array $row): string
    {
        $template = new ReportProcessor($this->templatePath, $this->templateModel->doc_type);
        foreach ($this->report->_sources as $source) {
            [$table, $sourceData] = $this->getSourceData($source, $row);
            foreach ($source->_variables as $variable) {
                if ($variable->variable_type == TableReportSourceVariable::TYPE_FIELD) {
                    $header = $table->_fields
                        ->where('id', '=', $variable->var_object_id)
                        ->first();
                    if ($header) {
                        $path = $header->f_type == 'Attachment'
                            ? storage_path('app/public/'.($sourceData[$header->field] ?? ''))
                            : '';
                        if ($header->f_type == 'Attachment' && file_exists($path)) {
                            $template->replaceVarImage($variable->variable, $path);
                        } else {
                            $template->replaceVariable($variable->variable, $sourceData[$header->field] ?? '');
                        }
                    }
                }
                if ($variable->variable_type == TableReportSourceVariable::TYPE_BI) {
                    $biImage = storage_path('app/bi_images_report/'.auth()->id().'/'.$table->id.'/'.$row['id'].'/'.$variable->var_object_id.'.png');
                    if (file_exists($biImage)) {
                        $template->replaceVarImage($variable->variable, $biImage);
                    }
                }
                if ($variable->variable_type == TableReportSourceVariable::TYPE_ROWS) {
                    $xml = $this->getOfficeTable($source, $row);
                    $template->replaceVarTable($variable->variable, $xml);
                }
            }
        }
        return $template->savedContent();
    }

    /**
     * @param TableReportSource $source
     * @param array $row
     * @return OfficeTable
     */
    protected function getOfficeTable(TableReportSource $source, array $row): OfficeTable
    {
        //Receive rows sql
        $refCond = $source->_ref_link ? $source->_ref_link->_ref_condition : null;
        $table = $refCond ? $refCond->_ref_table : $this->table;
        $sql = new TableDataQuery($table, true, auth()->id());
        if ($refCond) {
            $sql->applyRefConditionRow($refCond, $row);
        }
        //$sql->checkAndApplyDataRange();
        $sql = $sql->getQuery();

        $c2m2flds = (new HelperService())->c2m2_fields;
        $tableHeaders = (new TableFieldRepository())->getFieldsWithHeaders($table, auth()->id());
        $tableHeaders = $tableHeaders->filter(function($item) use ($c2m2flds) {
            return !in_array($item->field, $c2m2flds);
        });

        //Create table
        $docTable = new OfficeTable();
        $docTable->addRow();
        foreach ($tableHeaders as $header) {
            $docTable->addCell()->addText($header->name);
        }

        //Fill table by rows
        $packLen = 100;
        $lines = $sql->count();
        for ($cur = 0; ($cur * $packLen) < $lines; $cur++) {

            $allRows = $sql->offset($cur * $packLen)
                ->limit($packLen)
                ->get()
                ->toArray();

            foreach ($allRows as $oneRow) {
                $docTable->addRow();
                foreach ($tableHeaders as $header) {
                    $docTable->addCell()->addText($oneRow[$header->field] ?? '');
                }
            }
        }
        return $docTable;
    }

    /**
     * @param TableReportSource $source
     * @param array $row
     * @return array
     */
    protected function getSourceData(TableReportSource $source, array $row): array
    {
        if ($source->_ref_link) {
            $refCond = $source->_ref_link->_ref_condition;
            $refRows = (new TableDataRepository())->getReferencedRows($refCond, $row, ['*'], 1);
            return [$refCond->_ref_table, $refRows->first()];
        } else {
            return [$this->table, $row];
        }
    }

    /**
     * @param array $row
     * @return string
     */
    protected function reportFilename(array $row): string
    {
        $fileForm = $this->report->report_file_formula;
        /*if ($fileForm && !in_array($fileForm[0], ["'", '"'])) {
            $fileForm = '"' . $fileForm . '"';
        }*/

        $evaluator = new FormulaEvaluatorRepository($this->table, auth()->id());
        $filename = $fileForm ? $evaluator->formulaReplaceVars($row, $fileForm, true) : 'report';
        if ($this->templateModel->cloud_report_folder) {
            $filename = $row['id'] . $filename;
        }
        $ext = $this->docExt();

        if ($ext) {
            return preg_match('/\.' . $ext . '$/i', $filename)
                ? $filename
                : ($filename . '.' . $ext);
        } else {
            return $filename;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function availVariables(): array
    {
        return (new ReportProcessor($this->templatePath, $this->templateModel->doc_type))->allVariables();
    }
}