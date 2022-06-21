<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableFieldService;

class RisaCalculateLoadsController extends Controller implements AppControllerInterface
{
    /**
     * @var mixed|string
     */
    protected $url = '';

    /**
     * @var BladeVariablesService
     */
    protected $bladeVariablesService;

    /**
     * CallApiDesignController constructor.
     */
    public function __construct()
    {
        $this->bladeVariablesService = new BladeVariablesService();
        $this->bladeVariablesService->is_app_route = 1;
        $this->url = env('API_TIA_DESIGN_URL', 'https://tablda.com/_external_api_/tia_222/api_design.php');
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $tds = new TableDataService();
        $errors_present = [];
        $targetfile = $ma_table = $fld = $link_rows = $link = $link_table = $r3dfile = null;

        $serv = new HelperService();
        $corrTB = $correspApp->_tables()->where('row_hash', '!=', $serv->sys_row_hash['cf_temp'])->first();
        $corrFields = $corrTB->_fields()->where('row_hash', '!=', $serv->sys_row_hash['cf_temp'])->get();

        $jsonfile = storage_path('app/public/' . $request->json);
        if (!file_exists($jsonfile)) {
            $errors_present[] = 'Link Param "json" not present or Input Json is empty!';
        }

        try {
            $fld = (new TableFieldService())->getField($request->model_col);
            $fld->load('_table', '_links');

            $link = $fld->_links->first();
            $link_table = (new TableRepository())->getTable($link->_ref_condition->ref_table_id);
        } catch (\Exception $e) {
            $errors_present[] = 'Link Param "model_col" not present or doesn`t have Link to Geometry!';
        }

        $row = null;
        try {
            $ma_table = $fld->_table;
            $row = $tds->getDirectRow($ma_table, $request->row_id);

            [$rc, $link_rows] = $tds->getFieldRows($link_table, $link->toArray(), $row->toArray(), 1, 250);
            $link_rows = array_first($link_rows);
        } catch (\Exception $e) {
            $errors_present[] = 'Link Param "row_id" not present or not present Geometry Row!';
        }

        try {
            $r3d_fld = $link_table->_fields()->where('formula_symbol', '=', $request->r3d_formula_symbol)->first();
            $r3dfile = storage_path('app/public/' . $link_rows[$r3d_fld->field]);
            if (!file_exists($r3dfile)) {
                throw new \Exception('');
            }
        } catch (\Exception $e) {
            $errors_present[] = 'Link Param "r3d_formula_symbol" not present or Geometry Row doesn`t have R3D file in that column!';
        }

        try {
            $filename = preg_replace('/.r3d$/i', 'w_loading.r3d', basename($r3dfile));
            $content = file_get_contents($r3dfile);
            $copied_file = (new FileRepository())->insertFileAlias($ma_table->id, $request->result_col, $request->row_id, $filename, $content);
            $targetfile = storage_path('app/public/' . $copied_file->filepath . $copied_file->filename);
        } catch (\Exception $e) {
            $errors_present[] = 'Link Param "result_col" not present or r3d File didn`t copied!';
        }

        $usergroup_fld = $corrFields->where('app_field', '=', 'usergroup')->first();
        $model_fld = $corrFields->where('app_field', '=', 'model')->first();
        $usergroup = $row && $usergroup_fld ? $row->{$usergroup_fld->data_field} : '';
        $model = $row && $model_fld ? $row->{$model_fld->data_field} : '';

        $lightweight = $correspApp->open_as_popup;
        $app = CorrespApp::where('code', '=', 'call_api_design')->first();
        return view('tablda.applications.stim-calculate-loads', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'embed' => $lightweight,
                'apppath' => '/apps'.$app->app_path,
                'tiapath' => $this->url,
                'jsonfile' => $jsonfile,
                'targetfile' => $targetfile,
                'usergroup' => $usergroup,
                'model' => $model,
                'errors_present' => $errors_present,
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
        return new DirectCallOut();
    }
}
