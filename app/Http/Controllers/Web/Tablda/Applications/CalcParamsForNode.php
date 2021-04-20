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

class CalcParamsForNode extends Controller implements AppControllerInterface
{
    protected $settings = [];

    /**
     * CalcParamsForNode constructor.
     */
    public function __construct()
    {
        $this->settings = Settinger::get('calc_params_for_node');
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
        $app_table = $receiver->getTableWithMaps('parameters');
        $maps = $app_table['_app_maps'];

        $row = $input->getRow();
        if ($row[$maps['restrict']] ?? '') {
            if ($row[$maps['arithmetic']]) {
                $evaluator = new ExpressionLanguage();
                $row[$maps['value']] = $evaluator->evaluate($row[$maps['arithmetic']]);
            } else {
                $row[$maps['value']] = $row[$maps['dim_value']];
            }
        }
        $row[$maps['restrict']] = null;

        $out = new DirectCallOut();
        $out->setRow( $row );
        return $out;
    }
}
