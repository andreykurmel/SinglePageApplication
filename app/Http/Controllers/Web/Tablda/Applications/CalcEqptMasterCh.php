<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\TableDataService;

class CalcEqptMasterCh extends Controller implements AppControllerInterface
{
    protected $settings = [];

    /**
     * CalcParamsForNode constructor.
     */
    public function __construct()
    {
        $this->settings = Settinger::get('calc_eqpt_master');
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
        $master_table = $receiver->getTableWithMaps('master');
        $master_maps = $master_table['_app_maps'];
        $target_table = $receiver->getTableWithMaps('target');
        $target_maps = $target_table['_app_maps'];

        $row = $input->getRow();
        $old_row = $input->getOldRow();

        if ($row && $old_row) {
            $elev = $row[$master_maps['origin_elev']] ?? '';
            $old_elev = $old_row[$master_maps['origin_elev']] ?? '';

            if ($elev != $old_elev) {
                $ug = $row[$master_maps['usergroup']] ?? '';
                $loa = $row[$master_maps['loading']] ?? '';
                $tar_tb = (new TableRepository())->getTableByDB($target_table['data_table']);
                (new TableDataQuery($tar_tb))->getQuery()->where($target_maps['usergroup'], '=', $ug)
                    ->where($target_maps['loading'], '=', $loa)
                    ->update([$target_maps['dist'] => null]);
                (new TableDataService())->newTableVersion($tar_tb);
            }
        }

        $out = new DirectCallOut();
        $out->setRow( $row );
        return $out;
    }
}
