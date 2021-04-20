<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Tablda\DataReceiver\TabldaDataReceiver;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimMaJson\JsonService;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;

class CalcEqptOnChange extends Controller implements AppControllerInterface
{
    protected $settings = [];

    /**
     * CalcParamsForNode constructor.
     */
    public function __construct()
    {
        $this->settings = Settinger::get('calc_eqpt_params');
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        //
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
        $receiver = new TabldaDataReceiver($this->settings);
        $app_table = $receiver->getTableWithMaps('eqpts');
        $maps = $app_table['_app_maps'];

        $row = $input->getRow();
        $old_row = $input->getOldRow();

        if ($row && $old_row) {
            $dist = $row[$maps['dist_to_node']] ?? '';
            $old_dist = $old_row[$maps['dist_to_node']] ?? '';
            $gctr = $row[$maps['gctr_el']] ?? '';
            $old_gctr = $old_row[$maps['gctr_el']] ?? '';

            //changed 'Dist to node'
            if ($dist != $old_dist && $gctr == $old_gctr) {
                $row[$maps['gctr_el']] = '';
            }
            //changed 'Gctr'
            if ($dist == $old_dist && $gctr != $old_gctr) {
                $row[$maps['dist_to_node']] = '';
            }
        }

        $out = new DirectCallOut();
        $out->setRow( $row );
        return $out;
    }
}
