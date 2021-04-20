<?php

namespace Vanguard\AppsModules\StimWid;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\Rts\RtsInterface;
use Vanguard\AppsModules\StimWid\Rts\RtsRotate;
use Vanguard\AppsModules\StimWid\Rts\RtsScale;
use Vanguard\AppsModules\StimWid\Rts\RtsTranslate;
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

class SectionsParser
{
    protected $add_fld = '';
    protected $name_fld = '';
    protected $shape_fld = '';
    protected $size1_fld = '';
    protected $size2_fld = '';

    protected $app_table_string = '';
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

            try {
                for ($i = 0; $i*$chunk < $len; $i++) {
                    $rows = (clone $sql)->offset($i * $chunk)->limit($chunk)->get();
                    foreach ($rows as $row) {
                        $fields = $this->calc_row( $row->toArray() );
                        $repo->quickUpdate($table, $row->id, $fields);
                    }
                }
            } catch (\Exception $e) {
                return 'Row calculation error';
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
        $this->name_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:name', false, true);
        $this->add_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:add', false, true);
        $this->shape_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:shape', false, true);
        $this->size1_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:size1', false, true);
        $this->size2_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:size2', false, true);
        $this->type_fld = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_sec:type', false, true);

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
        $parsed_str = $row[$this->name_fld] . ' ' . ($row[$this->add_fld] ?? '');
        $dot_str = $this->replace_dots($parsed_str);
        $div_str = $this->replace_divs($parsed_str);
        if ($dot_str == $div_str) {
            $row = $this->parse_actions($dot_str, $row);
        } else {
            $row = $this->parse_actions($div_str, $row);
            $row = $this->parse_actions($dot_str, $row);
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
            $row = $this->set_aisc_to_row($row, $found);
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
     * @return array
     */
    protected function find_aisc(string $like_str)
    {
        return DataReceiver::mapped_query('AISC')
            ->where('AISC_Size2', 'like', $like_str)
            ->first();
    }

    /**
     * @param array $row
     * @param $aisc
     * @return array
     */
    protected function set_aisc_to_row(array $row, $aisc)
    {
        if ($aisc && is_array($aisc)) {
            $row[$this->shape_fld] = $aisc['AISC_Shape'];
            $row[$this->size1_fld] = $aisc['AISC_Size1'];
            $row[$this->size2_fld] = $aisc['AISC_Size2'];
        }
        return $row;
    }

}