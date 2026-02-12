<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vanguard\AppsModules\JsonImportExport3D\LoadingJson;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Services\Tablda\BladeVariablesService;

class Json3dImportExportController extends Controller implements AppControllerInterface
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
        $row_id = (int)$request->row_id;

        if ($row_id) {

            try {
                switch ($correspApp->app_path) {
                    case '/json-3d/loading/import':
                        $message = (new LoadingJson())->import($row_id);
                        break;
                    case '/json-3d/loading/export':
                        $message = (new LoadingJson())->export($row_id);
                        break;
                    default:
                        $message = 'Incorrect path!';
                        break;
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

        } else {
            $message = 'Not found "row_id"!';
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
