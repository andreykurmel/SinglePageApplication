<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Vanguard\AppsModules\StimMaJson\JsonService;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Services\Tablda\BladeVariablesService;

class StimMaJsonController extends Controller implements AppControllerInterface
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $errors_present = [];
        $warnings_present = [];
        $row_id = (int)$request->row_id;

        if ($row_id) {

            try {
                [$errors_present, $warnings_present] = (new JsonService())->makeFile($row_id);
            } catch (\Exception $e) {
                $errors_present[] = $e->getMessage();
            }

        } else {
            $errors_present[] = 'Not found "row_id"!';
        }

        $lightweight = $correspApp->open_as_popup;
        return view('tablda.applications.stim-ma-json', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'embed' => $lightweight,
                'errors_present' => $errors_present,
                'warnings_present' => $warnings_present,
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
