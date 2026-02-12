<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableCalendar;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableReport;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Services\Tablda\TableReportService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\Singletones\AuthUserSingleton;
use Vanguard\User;

class TableReportController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableReportService
     */
    protected $reportService;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->reportService = new TableReportService();
    }

    /**
     * @param Request $request
     * @return string
     * @throws Exception
     */
    public function makeReports(Request $request)
    {
        return $this->reportService->makeReports($request->report_id, $request->request_params, $request->direct_row_id ?: null);
    }

    /**
     * @param Request $request
     * @return array
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function templateVariables(Request $request)
    {
        return $this->reportService->templateVariables($request->report_id);
    }

    /**
     * @param Request $request
     * @return TableCalendar
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'report');
        $this->reportService->insert($table, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $report = $this->reportService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $report->_table;
        $this->canEditAddonItem($table, $report->_report_rights());
        $this->reportService->update($table, $request->model_id, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $report = $this->reportService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $report->_table;
        $this->canEditAddonItem($table, $report->_report_rights());
        $this->reportService->delete($request->model_id);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return TableCalendar
     * @throws AuthorizationException
     */
    public function insertTemplate(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'report');
        $this->reportService->insertTemplate($table, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_report_templates;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function updateTemplate(Request $request)
    {
        $report = $this->reportService->getTemplate($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $report->_table;
        $this->canEditAddon($table, 'report');
        $this->reportService->updateTemplate($table, $request->model_id, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_report_templates;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function deleteTemplate(Request $request)
    {
        $report = $this->reportService->getTemplate($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $report->_table;
        $this->canEditAddon($table, 'report');
        $this->reportService->deleteTemplate($request->model_id);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_report_templates;
    }

    /**
     * @param Request $request
     * @return TableCalendar
     * @throws AuthorizationException
     */
    public function insertSource(Request $request)
    {
        $report = $this->reportService->get($request->report_id);
        $table = $report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $report->_report_rights());
        $this->reportService->insertSource($report, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function updateSource(Request $request)
    {
        $source = $this->reportService->getSource($request->model_id);
        $table = $source->_report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $source->_report->_report_rights());
        $this->reportService->updateSource($source->_report, $request->model_id, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function deleteSource(Request $request)
    {
        $source = $this->reportService->getSource($request->model_id);
        $table = $source->_report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $source->_report->_report_rights());
        $this->reportService->deleteSource($request->model_id);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return TableCalendar
     * @throws AuthorizationException
     */
    public function insertVariable(Request $request)
    {
        $source = $this->reportService->getSource($request->report_source_id);
        $table = $source->_report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $source->_report->_report_rights());
        $this->reportService->insertSourceVariable($source, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function updateVariable(Request $request)
    {
        $variable = $this->reportService->getVariable($request->model_id);
        $table = $variable->_report_source->_report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $variable->_report_source->_report->_report_rights());
        $this->reportService->updateSourceVariable($variable->_report_source, $request->model_id, $request->fields);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function deleteVariable(Request $request)
    {
        $variable = $this->reportService->getVariable($request->model_id);
        $table = $variable->_report_source->_report->_table;
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($table, $variable->_report_source->_report->_report_rights());
        $this->reportService->deleteSourceVariable($request->model_id);
        $this->reportService->loadForTable($table, auth()->id());
        return $table->_reports;
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableReportRight
     * @throws AuthorizationException
     */
    public function toggleReportRight(Request $request)
    {
        $report = $this->reportService->get($request->report_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $report->_table]);
        return $this->reportService->toggleReportRight($report, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delReportRight(Request $request)
    {
        $report = $this->reportService->get($request->report_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $report->_table]);
        return $this->reportService->deleteReportRight($report, $request->permission_id);
    }
}
