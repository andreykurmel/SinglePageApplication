<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Tablda\DataReceiver\TabldaDataInterface;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\TabldaApps\Risa3dDeleter;

class Risa3dDeleterController extends Controller implements AppControllerInterface
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
        $settings = Settinger::get('risa3d_deleter');
        $interface = app(TabldaDataInterface::class, ['settings' => $settings]);
        $appDatas = $interface->appDatas();

        $tables_delete = [];
        foreach ($appDatas['_tables'] as $tbObject) {
            if ($tbObject->app_table != 'CALLING_URL_PARAMETERS') {
                $tables_delete[] = $tbObject->data_table;
            }
        }
        $tableNames = Table::whereIn('db_name', $tables_delete)->get()->pluck('name')->toArray();

        $lightweight = $correspApp->open_as_popup;
        return view('tablda.applications.risa3d-deleter', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'usergroup' => (new UserRepository())->getUserOrGroupInfo($request->usergroup),
                'mg_name' => $request->mg_name,
                'embed' => $lightweight,
                'tables_delete' => implode('<br>- ', $tableNames),
            ]
        ));
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function post(Request $request)
    {
        if (!$request->mg_name) {
            throw new \Exception('MG Name not found!');
        }

        try {
            $result = (new Risa3dDeleter())->remove($request->mg_name);
        } catch (\Exception $e) {
            $result = [$e->getMessage()];
        }
        return $result;
    }

    /**
     * @param DirectCallInput $input
     */
    public function direct_call(DirectCallInput $input)
    {
        // TODO: Implement direct_call() method.
    }

}
