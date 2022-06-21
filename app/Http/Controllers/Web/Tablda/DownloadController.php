<?php
/*From old project*/

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\DownloadService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class DownloadController extends Controller
{
    private $downloadService;
    private $tableService;

    /**
     * DownloadController constructor.
     *
     * @param DownloadService $downloadService
     * @param TableService $tableService
     */
    public function __construct(DownloadService $downloadService, TableService $tableService) {
        $this->downloadService = $downloadService;
        $this->tableService = $tableService;
    }

    /**
     * Download table data.
     *
     * @param Request $request
     * @return string
     */
    public function download(Request $request) {
        ini_set('memory_limit', '2048M');
        set_time_limit ( 1800 );

        $time = $request->time_zone ?: date("Y-m-d H:i:s");

        $request->data = json_decode($request->data, 1);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $this->tableService->getTable($request->data['table_id']);

        if ($table && in_array($request->filename, ['CSV','XLSX','PDF','JSON','XML'])) {
            $this->authorizeForUser($user, 'get', [TableData::class, $table]);
            // force download
            header("Content-Type: application/force-download");

            header("Content-Disposition: attachment;filename=" . $table->name . " " . $time . "." . $request->filename);
            header("Content-Transfer-Encoding: binary");

            $this->downloadService->download($table, $request->filename, $request->data, $time);
        }
        else {
            return "<h1>Incorrect request</h1>";
        }
        exit;
    }

    /**
     * Download Chart.
     *
     * @param Request $request
     * @return string
     */
    public function downloadChart(Request $request) {
        ini_set('memory_limit', '2048M');
        set_time_limit ( 1800 );

        $time = $request->time_zone ?: date("Y-m-d H:i:s");
        $tableHeaders = json_decode($request->chart_headers, 1);
        $tableRows = json_decode($request->chart_rows, 1);
        $filename = $request->file_name ?? "chart";

        if (count($tableHeaders) && count($tableRows)) {
            switch (strtolower($request->format_type)) {
                case 'pdf': $this->downloadService->pdfChart($tableHeaders, $tableRows, $filename." ".$time.".pdf");
                    break;
                case 'csv': $this->downloadService->csvChart($tableHeaders, $tableRows, $filename." ".$time.".csv");
                    break;
                default: return '<h1>Incorrect request file type</h1>';
                    break;
            }
        }
        else {
            return "<h1>Incorrect request</h1>";
        }
        exit;
    }
}
