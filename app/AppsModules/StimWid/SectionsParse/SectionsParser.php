<?php

namespace Vanguard\AppsModules\StimWid\SectionsParse;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\Rts\RtsInterface;
use Vanguard\AppsModules\StimWid\Rts\RtsRotate;
use Vanguard\AppsModules\StimWid\Rts\RtsScale;
use Vanguard\AppsModules\StimWid\Rts\RtsTranslate;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;
use Vanguard\Classes\MselConvert;
use Vanguard\Http\Controllers\Web\Tablda\TableDataController;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

/**
 * NOTES:
 * in Correspondence Table/Fields must be present:
 * - Table "AISC" with Fields [AISC_Shape, AISC_Size1, AISC_Size2, b, b_f, B_upr, d, Ht, OD]
 * - Table "{$Sections Table}" with Fields [sec_name, sec_add, shape, size1, size2, ex_d, ex_ht, ex_od, ex_b, ex_bf, ex_bupr, ex_dim1, ex_dim2, ex_wind, ex_d_c, ex_flat]
 */
class SectionsParser
{
    protected $add_fld;
    protected $name_fld;
    protected $shape_fld;
    protected $size1_fld;
    protected $size2_fld;

    protected $ex_d;
    protected $ex_ht;
    protected $ex_od;
    protected $ex_b;
    protected $ex_bf;
    protected $ex_bupr;
    protected $ex_dim1;
    protected $ex_dim2;
    protected $wind_proj;
    protected $calc_dc;
    protected $flat_round;

    protected $app_table_string;
    protected $app_tb = [];
    protected $req_params = [];

    /**
     * SectionsParser constructor.
     * @param string $app_table
     * @param array $req_params
     */
    public function __construct(string $app_table, array $req_params)
    {
        $this->app_table_string = $app_table;
        $this->req_params = $req_params;
    }

    /**
     * @return string
     */
    public function do_parse()
    {
        if (!$this->app_table_string || !$this->req_params) {
            return 'Incorrect request';
        }

        $err = $this->set_fields();
        if ($err) {
            return $err;
        }

        $table = (new TableRepository())->getTableByDB( $this->app_tb['data_table'] );
        if ($table) {
            $sql = new TableDataQuery($table);
            $sql->applyWhereClause($this->req_params, auth()->id());
            $sql = $sql->getQuery();

            $len = $sql->count();
            $chunk = 100;
            $repo = new TableDataRepository();

            for ($i = 0; $i*$chunk < $len; $i++) {
                $rows = (clone $sql)->offset($i * $chunk)->limit($chunk)->get();
                foreach ($rows as $row) {
                    $fields = $this->calc_row( $row->toArray() );
                    $repo->quickUpdate($table, $row->id, $fields);
                }
            }
        } else {
            return 'Table not found';
        }
        return '';
    }

    /**
     * @return string
     */
    protected function set_fields()
    {
        $stimRepo = new StimSettingsRepository();
        $this->app_tb = DataReceiver::app_table_and_fields($this->app_table_string);
        /**
         * @var Collection $fields
         */
        $fields = $this->app_tb['_app_fields'];
        if ($fields && $fields->count()) {
            $this->name_fld = $fields->where('app_field', '=', 'sec_name')->first();
            $this->add_fld = $fields->where('app_field', '=', 'sec_add')->first();
            $this->shape_fld = $fields->where('app_field', '=', 'shape')->first();
            $this->size1_fld = $fields->where('app_field', '=', 'size1')->first();
            $this->size2_fld = $fields->where('app_field', '=', 'size2')->first();

            $this->ex_d = $fields->where('app_field', '=', 'ex_d')->first();
            $this->ex_ht = $fields->where('app_field', '=', 'ex_ht')->first();
            $this->ex_od = $fields->where('app_field', '=', 'ex_od')->first();
            $this->ex_bf = $fields->where('app_field', '=', 'ex_bf')->first();
            $this->ex_bupr = $fields->where('app_field', '=', 'ex_bupr')->first();
            $this->ex_b = $fields->where('app_field', '=', 'ex_b')->first();
            $this->ex_dim1 = $fields->where('app_field', '=', 'ex_dim1')->first();
            $this->ex_dim2 = $fields->where('app_field', '=', 'ex_dim2')->first();

            $this->wind_proj = $fields->where('app_field', '=', 'ex_wind')->first();
            $this->calc_dc = $fields->where('app_field', '=', 'ex_d_c')->first();
            $this->flat_round = $fields->where('app_field', '=', 'ex_flat')->first();
        }

        if (!$this->name_fld || !$this->shape_fld || !$this->size1_fld || !$this->size2_fld) {
            return 'Incorrect settings in Correspondence/Fields.tbl (Sections)';
        }
        return '';
    }

    /**
     * replace '2.5' => '2-1/2'
     *
     * @param $string
     * @return mixed
     */
    protected function replace_dots($string)
    {
        return preg_replace_callback('/([\d]+)(\.[\d]+)/i', function ($arr) {
            $left = intval($arr[1]);
            $right = floatval($arr[2]);
            $result = $left ? (string)$left : '';
            $result .= $left && $right ? '-' : '';
            $result .= $right ? '1/'.(1/$right) : '';
            return $result;
        }, $string);
    }

    /**
     * replace '2-1/2' => '2.5'
     *
     * @param $string
     * @return mixed
     */
    protected function replace_divs($string)
    {
        $changed = preg_replace_callback('/([\d]+)\/([\d]+)/i', function ($arr) {
            $left = intval($arr[1]);
            $right = intval($arr[2]);
            $result = (string)($left/$right);
            return $result;
        }, $string);
        return preg_replace_callback('/([\d\.]+)-([\d\.]+)/i', function ($arr) {
            $left = floatval($arr[1]);
            $right = floatval($arr[2]);
            $result = (string)($left+$right);
            return $result;
        }, $changed);
    }

    /**
     * @param array $row
     * @return array
     */
    protected function calc_row(array $row): array
    {
        $parsed_str = $row[$this->name_fld->data_field] . ' ' . ($row[$this->add_fld->data_field] ?? '');
        $parsed_str = preg_replace('/[^a-z.\/\d-]/i', '', $parsed_str);
        $dot_str = $this->replace_dots($parsed_str);
        $div_str = $this->replace_divs($parsed_str);
        if ($dot_str == $div_str) {
            $row = $this->parse_actions($dot_str, $row);
        } else {
            $row = $this->parse_actions($dot_str, $row);
            $row = $this->parse_actions($div_str, $row);
        }
        return $row;
    }

    /**
     * @param string $parsed_str
     * @param array $row
     * @return array
     */
    protected function parse_actions(string $parsed_str, array $row): array
    {
        $pos = [];
        //WIDE FLANGE
        if (preg_match('/wide|flange/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);
            $found = $mch ? $this->find_aisc('%W'.array_first($mch).'%') : null;
            $row = $this->set_aisc_to_row($row, $found);
        }
        //DOUBLE ANGLE
        if (
            (preg_match('/double/i', $parsed_str) && preg_match('/angle/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE))
            ||
            (preg_match('/LL/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE))
        ) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);
            $found = $mch ? $this->find_aisc('%2L'.array_first($mch).'%') : null;
            $row = $this->set_aisc_to_row($row, $found);
        }
        //SINGLE ANGLE
        if (preg_match('/angle/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);
            $found = $mch ? $this->find_aisc('%L'.array_first($mch).'%') : null;
            $row = $this->set_aisc_to_row($row, $found);
        }
        //PIPE
        if (preg_match('/PIPE|STD|XS|XXS/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+/i', $parsed_str, $mch);
            $type = preg_match('/XXS/i', $parsed_str) ? 'XXS'
                : (preg_match('/XS/i', $parsed_str) ? 'XS' : 'STD');
            $found = $mch ? $this->find_aisc('%PIPE'.array_first($mch).$type.'%') : null;
            $row = $this->set_aisc_to_row($row, $found);
        }
        //HSS
        if (preg_match('/HSS/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);//3x3x3
            $found = $mch ? $this->find_aisc('%HSS'.array_first($mch).'%') : null;
            if (!$found) {
                preg_match('/[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);//3x3
                $found = $mch ? $this->find_aisc('%HSS' . array_first($mch) . '%') : null;
            }
            $row = $this->set_aisc_to_row($row, $found);
        }
        //SR
        if (preg_match('/SR/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+/i', $parsed_str, $mch);
            $found = $mch ? $this->find_aisc('%SR'.array_first($mch)) : null;
            if (!$found && $mch) {
                $found = $this->find_aisc_by_calc('%SR%', 'SR'.array_first($mch));
            }
            $row = $this->set_aisc_to_row($row, $found);
        }
        //C
        if (preg_match('/C/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);//3x3
            $found = $mch ? $this->find_aisc('C'.array_first($mch)) : null;
            $row = $this->set_aisc_to_row($row, $found);
        }
        //PLATE
        if (preg_match('/PL|PLATE/i', $parsed_str, $pos, PREG_OFFSET_CAPTURE)) {
            $parsed_str = $this->onlyRight($parsed_str, $pos);
            $mch = [];
            preg_match('/[\d-\/\.]+x[\d-\/\.]+/i', $parsed_str, $mch);//3x3
            $mch = $mch ? array_first($mch) : '';
            $found = $mch ? $this->find_aisc('PL'.$mch) : null;
            $dims = explode('x', strtolower($mch));
            $row = $this->set_aisc_to_row($row, $found??['id'=>0], $dims[0]??0, $dims[1]??0);
        }

        return $row;
    }

    /**
     * @param string $parsed_str
     * @param array $pos
     * @return bool|string
     */
    protected function onlyRight(string $parsed_str, array $pos)
    {
        $start = array_first($pos)[1] ?? 0;
        return substr($parsed_str, $start);
    }

    /**
     * @param string $like_str
     * @param bool $all
     * @return array|null
     */
    protected function find_aisc_by_calc(string $like_str, string $search)
    {
        $rows = $this->find_aisc($like_str, true);
        foreach ($rows as $row) {
            if ($search == $this->replace_divs($row['AISC_Size2'])) {
                return $row;
            }
        }
        return null;
    }

    /**
     * @param string $like_str
     * @param bool $all
     * @return array|null
     */
    protected function find_aisc(string $like_str, bool $all = false)
    {
        $sql = DataReceiver::mapped_query('AISC')
            ->where('AISC_Size2', 'like', $like_str);
        return $all ? $sql->get() : $sql->first();
    }

    /**
     * @param array $row
     * @param $aisc
     * @param int $dim1
     * @param int $dim2
     * @return array
     */
    protected function set_aisc_to_row(array $row, $aisc, $dim1 = 0, $dim2 = 0)
    {
        if ($aisc && is_array($aisc)) {
            $row[$this->shape_fld->data_field] = $aisc['AISC_Shape'] ?? '';
            $row[$this->size1_fld->data_field] = $aisc['AISC_Size1'] ?? '';
            $row[$this->size2_fld->data_field] = $aisc['AISC_Size2'] ?? '';

            if ($this->ex_b && !empty($aisc['b'])) {
                $row[$this->ex_b->data_field] = $aisc['b'];
            }
            if ($this->ex_bf && !empty($aisc['b_f'])) {
                $row[$this->ex_bf->data_field] = $aisc['b_f'];
            }
            if ($this->ex_bupr && !empty($aisc['B_upr'])) {
                $row[$this->ex_bupr->data_field] = $aisc['B_upr'];
            }
            if ($this->ex_d && !empty($aisc['d'])) {
                $row[$this->ex_d->data_field] = $aisc['d'];
            }
            if ($this->ex_ht && !empty($aisc['Ht'])) {
                $row[$this->ex_ht->data_field] = $aisc['Ht'];
            }
            if ($this->ex_od && !empty($aisc['OD'])) {
                $row[$this->ex_od->data_field] = $aisc['OD'];
            }

            [$dim1_a, $dim2_a, $flat] = $this->getDims($aisc['AISC_Shape'] ?? '', $aisc);
            $dim1 = floatval($dim1_a ?: $dim1);
            $dim2 = floatval($dim2_a ?: $dim2);
            if ($this->ex_dim1) {
                $row[$this->ex_dim1->data_field] = $dim1;
            }
            if ($this->ex_dim2) {
                $row[$this->ex_dim2->data_field] = $dim2;
            }

            if ($this->wind_proj) {
                $row[$this->wind_proj->data_field] = max($dim1, $dim2);
            }
            if ($this->calc_dc) {
                $row[$this->calc_dc->data_field] = sqrt(pow($dim1, 2) + pow($dim2, 2));
            }
            if ($this->flat_round) {
                $row[$this->flat_round->data_field] = $flat;
            }
        }
        return $row;
    }

    /**
     * @param string $shape
     * @param $aisc
     * @return array
     */
    protected function getDims(string $shape, $aisc): array
    {
        $dim1_v = 0;
        $dim2_v = 0;
        $flat_round = '';
        switch (strtolower($shape)) {

            case "hss_round":
            case "hss_rnd":
            case "pipe":
            case "sr":
                $dim1_v = $aisc['OD'] ?? 0;
                $dim2_v = 0;
                $flat_round = "Round";
                break;

            case "hss_sqr":
            case "hss_rect":
                $dim1_v = $aisc['Ht'] ?? 0;
                $dim2_v = $aisc['B_upr'] ?? 0;
                $flat_round = "Flat";
                break;

            case "bar":
            case "flat bar":

            case "pl":
            case "plate":

            case "l":
            case "l_e":
            case "l_ue":
                $dim1_v = $aisc['b'] ?? 0;
                $dim2_v = $aisc['d'] ?? 0;
                $flat_round = "Flat";
                break;

            case "2l":
            case "2l_e":
            case "2l_ue":
                $dim1_v = 2*min($aisc['b'] ?? 0, $aisc['d'] ?? 0);
                $dim2_v = max($aisc['b'] ?? 0, $aisc['d'] ?? 0);
                $flat_round = "Flat";
                break;

            case "2l_slbb":
                $dim1_v = 2*($aisc['b'] ?? 0);
                $dim2_v = $aisc['d'] ?? 0;
                $flat_round = "Flat";
                break;

            case "2l_llbb":
                $dim1_v = $aisc['b'] ?? 0;
                $dim2_v = 2*($aisc['d'] ?? 0);
                $flat_round = "Flat";
                break;

            case "w":
            case "m":
            case "s":
            case "hp":
            case "c":
            case "mc":
            case "wt":
            case "mt":
            case "st":
                $dim1_v = $aisc['d'] ?? 0;
                $dim2_v = $aisc['b_f'] ?? 0;
                $flat_round = "Flat";
                break;

            case "rigid":
            case "rl":
            default:
                $dim1_v = 0;
                $dim2_v = 0;
                $flat_round = "Flat";
                break;

        }
        return [$dim1_v, $dim2_v, $flat_round];
    }

}