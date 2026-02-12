<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vanguard\AppsModules\GeneralJson\GeneralJsonImportExport;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;

class GeneralJsonImportExportController extends Controller implements AppControllerInterface
{

    private $bladeVariablesService;

    public function __construct(BladeVariablesService $bladeVariablesService)
    {
        $this->bladeVariablesService = $bladeVariablesService;
        $this->bladeVariablesService->is_app_route = 1;
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return Factory|View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $table_id = (int)$request->table_id;
        $row_id = (int)$request->row_id;
        $file_field_id = (int)$request->file_field_id;

        if ($table_id && $row_id && $file_field_id) {

            try {
                $table = (new TableRepository())->getTable($table_id);
                switch ($correspApp->app_path) {
                    case '/general-json/import':
                        $message = (new GeneralJsonImportExport($table))->import($row_id, $file_field_id);
                        break;
                    case '/general-json/export':
                        $message = (new GeneralJsonImportExport($table))->export($row_id, $file_field_id, $request->link_id ?: 0);
                        break;
                    default:
                        $message = 'Incorrect path!';
                        break;
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

        } else {
            $message = 'Not found "table_id", "row_id" or "file_field_id"!';
        }

        $lightweight = $correspApp->open_as_popup;
        return view('tablda.applications.simple-messager', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'embed' => $lightweight,
                'messages' => [$message],
                'page_title' => $correspApp->name,
                'no_settings' => 1,
            ]
        ));
    }

    /**
     * @param Request $request
     */
    public function post(Request $request)
    {
        //
    }

    /**
     * @param DirectCallInput $input
     * @return DirectCallOut
     */
    public function direct_call(DirectCallInput $input)
    {
        return (new DirectCallOut());
    }

}
