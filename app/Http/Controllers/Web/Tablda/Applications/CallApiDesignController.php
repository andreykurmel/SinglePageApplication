<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlFactory;
use function GuzzleHttp\Psr7\build_query;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use JsonSchema\Uri\Retrievers\Curl;
use Vanguard\AppsModules\StimMaJson\JsonService;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\TabldaApps\Risa3dWriterRLS;

class CallApiDesignController extends Controller implements AppControllerInterface
{
    /**
     * @var mixed|string
     */
    protected $url = '';

    /**
     * CallApiDesignController constructor.
     */
    public function __construct()
    {
        $this->url = env('API_TIA_DESIGN_URL', 'https://tablda.com/_external_api_/tia_222/api_design.php');
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $serv = new HelperService();
        $corrTB = $correspApp->_tables()->where('row_hash', '!=', $serv->sys_row_hash['cf_temp'])->first();
        $corrFields = $corrTB->_fields()->where('row_hash', '!=', $serv->sys_row_hash['cf_temp'])->get();

        $meta_table = (new TableRepository())->getTableByDB($corrTB->data_table);
        $sql = (new UserPermisQuery($meta_table))->getQuery();
        $can = false;
        foreach ($corrFields as $cFld) {
            if (preg_match('/in_url:true/i', $cFld->options)) {
                $sql->where($cFld->data_field, '=', $request[$cFld->app_field]);
                $can = true;
            }
        }
        $row = $can ? $sql->first() : null;

        //recreate json-file
        if ($request->rejson && $row) {
            try {
                [$errors_present, $warnings_present] = (new JsonService())->makeFile($row->id);
                $row = $can ? $sql->first() : null;
            } catch (\Exception $e) {
                $errors_present[] = $e->getMessage();
            }
            if ($errors_present) {
                return implode('<br>', $errors_present);
            }
        }

        $json_fld = $corrFields->where('app_field', '=', 'json')->first();
        $risa_fld = $corrFields->where('app_field', '=', 'r3d')->first();

        $json_path = $row && $json_fld ? $row->{$json_fld->data_field} : '';
        $risa_path = $row && $risa_fld ? $row->{$risa_fld->data_field} : '';

        if ($json_path) {
            $abs_path = storage_path('app/public/');

            //add RLs
            if ($request->rls) {
                $usr_fld = $corrFields->where('app_field', '=', 'usergroup')->first();
                $geom_fld = $corrFields->where('app_field', '=', 'structure')->first();
                $usrdata = $row && $usr_fld ? $row->{$usr_fld->data_field} : '';
                $geomdata = $row && $geom_fld ? $row->{$geom_fld->data_field} : '';
                (new Risa3dWriterRLS($usrdata, $geomdata, $correspApp))->writeRLs($abs_path.$risa_path);
            }

            $params = [
                'inp_type' => 'stim_page',
                'json' => $abs_path.$json_path,
                'r3d' => $risa_path ? $abs_path.$risa_path : '',
                'noupd' => $request->noupd ? 1 : 0,
            ];
            return view('tablda.applications.iframe-app', array_merge(
                (new BladeVariablesService())->getVariables(),
                [
                    'iframe_path' => $this->url . '?' . Query::build($params),
                ]
            ));
        } else {
            return 'Input JSON file not found!';
        }
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
        $values = $this->rowToValues($input->getTable(), $input->getRow());

        $curl = new Client();
        $response = $curl->request('POST', $this->url, [
            'query' => [
                'inp_type' => 'array',
                'data' => json_encode($values),
            ]
        ]);
        $content = json_decode($response->getBody(), true);

        $out = new DirectCallOut();
        $out->setRow( $content ? $this->valuesToRow($input->getTable(), $content) : [] );
        return $out;
    }

    /**
     * @param Table $table
     * @param array $row
     * @return array
     */
    protected function rowToValues(Table $table, array $row)
    {
        $values = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr->formula_symbol) {
                $tmp = [ 'value' => $row[$hdr->field] ?? '' ];
                if ($hdr->unit && $hdr->unit_ddl_id) {
                    $tmp['unit'] = $hdr->unit;
                }
                if ($hdr->tooltip) {
                    $tmp['description'] = $hdr->tooltip;
                }
                $values[$hdr->formula_symbol] = $tmp;
            }
        }
        return $values;
    }

    /**
     * @param Table $table
     * @param array $values
     * @return array
     */
    protected function valuesToRow(Table $table, array $values)
    {
        $row = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr->formula_symbol) {
                $row[$hdr->field] = $values[$hdr->formula_symbol]['value'] ?? null;
            }
        }
        return $row;
    }
}
