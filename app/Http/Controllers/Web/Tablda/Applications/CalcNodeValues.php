<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Tablda\DataReceiver\TabldaDataReceiver;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;

class CalcNodeValues extends Controller implements AppControllerInterface
{
    protected $settings = [];
    protected $param_links = [];
    /**
     * @var FormulaEvaluatorRepository
     */
    protected $evaluator;

    /**
     * CalcParamsForNode constructor.
     */
    public function __construct()
    {
        $this->settings = Settinger::get('calc_node_values');
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
        $app_table = $receiver->getTableWithMaps('nodes');
        $maps = $app_table['_app_maps'];

        $row = $input->getRow();
        $this->prepareParamLinks($receiver, $row[$maps['usergroup']] ?? '', $row[$maps['model']] ?? '');

        if ($row[$maps['restrict']] ?? '') {
            $row = $this->calcRow($maps, $row, $input);
        }
        $row[$maps['restrict']] = null;

        $out = new DirectCallOut();
        $out->setRow($row);
        return $out;
    }

    /**
     * @param TabldaDataReceiver $receiver
     * @param string $usergroup
     * @param string $model
     */
    protected function prepareParamLinks(TabldaDataReceiver $receiver, string $usergroup, string $model)
    {
        $app_params = $receiver->getTableWithMaps('params');
        $param_maps = $app_params['_app_maps'];

        $params_table = (new TableRepository())->getTableByDB($app_params['data_table']);

        $params = (new TableDataQuery($params_table))
            ->getQuery()
            ->where($param_maps['usergroup'], $usergroup)
            ->where($param_maps['model'], $model)
            ->get();

        foreach ($params as $param) {
            $key = $this->l_name($param[$param_maps['name']]);
            $this->param_links[$key] = $param_maps['value'];
        }
        $this->evaluator = new FormulaEvaluatorRepository($params_table, null, true);
    }

    /**
     * @param $name
     * @return string
     */
    private function l_name($name)
    {
        $name = preg_replace('/[\\\]/i', '', $name);//sanitize
        return strtolower($name);
    }

    /**
     * @param array $maps
     * @param array $row
     * @param DirectCallInput $input
     * @return array
     */
    protected function calcRow(array $maps, array $row, DirectCallInput $input): array
    {
        $node = $this->getNode($maps, $input, 'base_node');
        $x = $node[$maps['node_x']] ?? 0;
        $y = $node[$maps['node_y']] ?? 0;
        $z = $node[$maps['node_z']] ?? 0;

        [$x, $z] = $this->addWRT($input, $maps, 'wrt1', $x, $z);

        $x += $this->deltaCalc($row[$maps['delta_dx']] ?? 0);
        $y += $this->deltaCalc($row[$maps['delta_dy']] ?? 0);
        $z += $this->deltaCalc($row[$maps['delta_dz']] ?? 0);

        [$x, $z] = $this->addWRT($input, $maps, 'wrt2', $x, $z);

        $row[$maps['node_x']] = $x;
        $row[$maps['node_y']] = $y;
        $row[$maps['node_z']] = $z;

        return $row;
    }

    /**
     * @param array $maps
     * @param DirectCallInput $input
     * @param string $key
     * @return array|null
     */
    protected function getNode(array $maps, DirectCallInput $input, string $key = 'base_node')
    {
        $row = $input->getRow();

        $node = (new TableDataQuery($input->getTable()))
            ->getQuery()
            ->where($maps['usergroup'], $row[$maps['usergroup']])
            ->where($maps['model'], $row[$maps['model']])
            ->where($maps['node_name'], $row[$maps[$key]])
            ->first();

        $node = $node ? $node->toArray() : null;

        return $node;
    }

    /**
     * @param DirectCallInput $input
     * @param array $maps
     * @param string $key
     * @param $x
     * @param $z
     * @return array
     */
    protected function addWRT(DirectCallInput $input, array $maps, string $key, $x, $z)
    {
        $row = $input->getRow();
        $wrt = $this->getNode($maps, $input, $key.'_node');

        $wrt_x = $wrt_ix = $wrt[$maps['node_x']] ?? 0;
        $wrt_z = $wrt_iz = $wrt[$maps['node_z']] ?? 0;
        $roty = $row[ $maps[ $key.'_roty' ] ] ?? 0;
        if ($roty) {
            $ry = $roty * (M_PI / 180);
            $wrt_x = ($x - $wrt_ix) * cos($ry) - ($z - $wrt_iz) * sin($ry);
            $wrt_z = ($x - $wrt_ix) * sin($ry) + ($z - $wrt_iz) * cos($ry);
            $x = $wrt_x + $wrt_ix;
            $z = $wrt_z + $wrt_iz;
        }

        return [$x, $z];
    }

    /**
     * @param $formula
     * @return mixed
     */
    protected function deltaCalc($formula)
    {
        if ($formula && !is_numeric($formula)) {
            $formula = $this->formulaReplaceVars((string)$formula);
            $formula = $this->evaluator->justCalc($formula);
        }
        return $formula;
    }

    /**
     * @param string $formula_str
     * @return mixed|string
     */
    protected function formulaReplaceVars(string $formula_str)
    {
        $active_fields = [];
        preg_match_all('/\\{[^\\}]*\\}/i', $formula_str, $active_fields);
        $active_fields = $active_fields[0];

        foreach ($active_fields as $idx => $act_field) { // $act_field = '{FIELD_NAME}'
            $act_field = $this->l_name($act_field);
            $param_val = $this->param_links[$act_field] ?? 0;
            $formula_str = preg_replace('/' . $act_field . '/i', $param_val, $formula_str);
        }

        return $formula_str;
    }
}
