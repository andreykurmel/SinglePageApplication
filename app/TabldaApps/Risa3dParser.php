<?php

namespace Vanguard\TabldaApps;


use Ramsey\Uuid\Uuid;
use Tablda\DataReceiver\TabldaDataInterface;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Modules\Settinger;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

class Risa3dParser
{
    //NOTE: 'code' in 'correspondence_apps' must be 'risa3d_parser'

    protected $file_text = null;
    protected $mg_name = '';
    protected $usergroup = '';

    protected $errors = [];
    protected $memberTypes = [
        0 => 'None',
        1 => 'Beam',
        2 => 'Column',
        3 => 'HBrace',
        4 => 'VBrace',
    ];

    protected $materialTypes = [
        0 => 'General',
        1 => 'Hot Rolled',
        2 => 'Cold Formed',
        3 => 'Wood',
        4 => 'Masonry',
        5 => 'Concrete',
        6 => 'Aluminium',
        10 => 'Stainless',
    ];

    protected $sectionToMatTypes = [
        'GENERAL_SECTION_SETS' => 'General',
        'HR_STEEL_SECTION_SETS' => 'Hot Rolled',
        'CF_STEEL_SECTION_SETS' => 'Cold Formed',
        'WOOD_SECTION_SETS' => 'Wood',
        'MASONRY_SECTION_SETS' => 'Masonry',
        'CONCRETE_SECTION_SETS' => 'Concrete',
        'ALUMINUM_SECTION_SETS' => 'Aluminium',
        'STAINLESS_SECTION_SETS' => 'Stainless',
    ];
    protected $materialToMatTypes = [
        'GENERAL_MATERIAL' => 'General',
        'HR_STEEL_MATERIAL' => 'Hot Rolled',
        'CF_STEEL_MATERIAL' => 'Cold Formed',
        'WOOD_MATERIAL' => 'Wood',
        'MASONRY_MATERIAL' => 'Masonry',
        'CONCRETE_MATERIAL' => 'Concrete',
        'ALUMINUM_MATERIAL' => 'Aluminium',
        'S_STEEL_MATERIAL' => 'Stainless',
    ];

    protected $matFromSection = [];//flipped keys from $sectionFromMat
    protected $sectionFromMat = [
        'GENERAL_MATERIAL' => 'GENERAL_SECTION_SETS',
        'HR_STEEL_MATERIAL' => 'HR_STEEL_SECTION_SETS',
        'CF_STEEL_MATERIAL' => 'CF_STEEL_SECTION_SETS',
        'WOOD_MATERIAL' => 'WOOD_SECTION_SETS',
        'MASONRY_MATERIAL' => 'MASONRY_SECTION_SETS',
        'CONCRETE_MATERIAL' => 'CONCRETE_SECTION_SETS',
        'ALUMINUM_MATERIAL' => 'ALUMINUM_SECTION_SETS',
        'S_STEEL_MATERIAL' => 'STAINLESS_SECTION_SETS',
    ];

    protected $alTables = [
        0 => 'Table B.4-1',
        1 => 'Table B.4-2',
    ];
    protected $woodTypes = [
        0 => 'Standard',
        1 => 'Custom',
    ];
    protected $woodSpecies = [
        1 => 'Aspen',
        2 => 'Balsam Fir',
        3 => 'Beech-Birch-Hickory',
        4 => 'Coast Sitka Spruce',
        5 => 'Cottonwood',
        6 => 'Douglas Fir-Larch',
        7 => 'Douglas Fir-Larch (North)',
        8 => 'Douglas Fir- South',
        9 => 'Eastern Hemlock',
        10 => 'Eastern Hemlock-Tamarack',
        11 => 'Eastern Hemlock-Tamarack (N)',
        12 => 'Eastern Softwoods',
        13 => 'Eastern Spruce',
        14 => 'Eastern White Pine',
        15 => 'Hem-Fir',
        16 => 'Hem-Fir (North)',
        17 => 'Mixed Maple',
        18 => 'Mixed Oak',
        19 => 'Mixed Southern Pine',
        20 => 'Mountain Hemlock',
        21 => 'Northern Pine',
        22 => 'Northern Red Oak',
        23 => 'Northern Species',
        24 => 'Northern White Cedar',
        25 => 'Ponderosa Pine',
        26 => 'Red Maple',
        27 => 'Red Oak',
        28 => 'Red Pine',
        29 => 'Redwood',
        30 => 'Sitka Spruce ',
        31 => 'Southern Pine',
        32 => 'Spruce-Pine-fir',
        33 => 'Spruce-Pine-Fir (South)',
        34 => 'Western Cedars',
        35 => 'Western Cedars (North)',
        36 => 'Western Hemlock',
        37 => 'Western Hemlock (North)',
        38 => 'Western White Pine',
        39 => 'Western Woods',
        40 => 'White Oak',
        41 => 'Yellow Poplar',
    ];
    protected $woodGrades = [
        1 => 'Select Structural',
        2 => 'No.1',
        3 => 'No.2',
        4 => 'No.3',
        5 => 'Stud',
        6 => 'Construction',
        7 => 'Standard',
        8 => 'Utility',
        9 => 'Dense Select Structural',
        10 => 'Non-Dense Select Structural',
        11 => 'No.1 Dense',
        12 => 'No.1 Non-Dense',
        13 => 'No.2 Dense',
        14 => 'No.2 Non-Dense',
        15 => 'Dense Structural D86',
        16 => 'Dense Structural D72',
        17 => 'Dense Structural D65',
        18 => 'No.1 & Better',
        19 => 'Clear Structural',
        20 => 'Select Structural, Open Grain',
        21 => 'No.1, Open Grain',
        22 => 'No.2, Open Grain',
        23 => 'No.3, Open Grain',
    ];

    /**
     * Risa3dParser constructor.
     */
    public function __construct()
    {
        $this->matFromSection = array_flip($this->sectionFromMat);
    }

    /**
     * @param string $usergroup
     * @param string $mg_name
     * @param string $file_content
     * @return array
     * @throws \Exception
     */
    public function parse(string $usergroup, string $mg_name, string $file_content)
    {
        $this->file_text = $file_content;
        if (!$this->file_text) {
            throw new \Exception('FILE not found!');
        }

        $this->mg_name = $mg_name;
        $this->usergroup = $usergroup;
        if (!$this->mg_name || !$this->usergroup) {
            throw new \Exception('MG_NAME and USERGROUP are not present!');
        }


        $settings = Settinger::get('risa3d_parser');
        $interface = app(TabldaDataInterface::class, ['settings' => $settings]);
        $appDatas = $interface->appDatas();

        foreach ($appDatas['_tables'] as $tbObject) {
            foreach ($this->getAppTableArr($tbObject->app_table) as $app_table_str) {
                //parse from risa3d file.
                $collected_rows = $this->subsegment_parse($app_table_str);
                //save to the DataBase.
                foreach ($collected_rows as $col_row) {
                    $this->insert_in_tablda_table($tbObject, $col_row);
                }
                //update 'row_order'
                (new TableDataRepository())->recalcOrderRow($tbObject->data_table, 0);
            }
        }

        return $this->errors;
    }

    /**
     * Insert parsed data into the TablDA.
     *
     * @param $tabldaObject
     * @param array $all_values
     */
    private function insert_in_tablda_table($tabldaObject, array $all_values)
    {
        if (!count($all_values)) {
            return;
        }

        $all_values['MG_NAME'] = $this->mg_name;
        $all_values['USERGROUP'] = $this->usergroup;
        $all_values['_row_hash'] = Uuid::uuid4();

        $key_values = CorrespField::where('correspondence_app_id', $tabldaObject->correspondence_app_id)
            ->where('correspondence_table_id', $tabldaObject->id)
            ->where('options', 'like', '%key:true%')
            ->get()
            ->pluck('app_field')
            ->toArray();

        $settings = Settinger::get('risa3d_parser');
        $table = app(TabldaDataInterface::class, ['settings' => $settings])
            ->tableReceiver($tabldaObject->app_table, true);
        foreach ($key_values as $kv) {
            $table->where($kv, $all_values[$kv]);
        }

        if ($table->count()) {
            $table->update($all_values);
        } else {
            $table->insert($all_values);
        }
    }

    /**
     * @param string $app_table
     * @return array
     */
    protected function getAppTableArr(string $app_table):array
    {
        if ($app_table == 'CALLING_URL_PARAMETERS') {
            return [];//no parse
        }
        if ($app_table == 'SECTION_SETS') {
            return [
                'HR_STEEL_SECTION_SETS',
                'CF_STEEL_SECTION_SETS',
                'WOOD_SECTION_SETS',
                'GENERAL_SECTION_SETS',
                'CONCRETE_SECTION_SETS',
                'ALUMINUM_SECTION_SETS',
                'STAINLESS_SECTION_SETS',
                'MASONRY_SECTION_SETS',
            ];
        }
        if ($app_table == 'MATERIAL_PROPERTIES') {
            return [
                'ALUMINUM_MATERIAL',
                'WOOD_MATERIAL',
                'GENERAL_MATERIAL',
                'HR_STEEL_MATERIAL',
                'CF_STEEL_MATERIAL',
                'MASONRY_MATERIAL',
                'CONCRETE_MATERIAL',
                'S_STEEL_MATERIAL',
            ];
        }
        return [$app_table];
    }

    /**
     * Parse provided segment.
     *
     * @param string $subsegment
     * @return array
     */
    protected function subsegment_parse(string $subsegment)
    {
        $subsegment = strtoupper($subsegment);
        $parse_params = $this->getParseParams($subsegment);

        $data = $this->get_record_value($subsegment);
        $records = explode(";", $data);

        $result = [];
        foreach ($records as $index => $record) {
            $matchs = array();
            $record = trim($record);
            if (
                preg_match($parse_params, $record . " ", $matchs) and trim($record) != ""
            ) {
                $matchs = $this->clearNumericMatches($matchs);

                //additional functions
                switch ($subsegment) {
                    case 'HR_STEEL_SECTION_SETS':
                    case 'CF_STEEL_SECTION_SETS':
                    case 'WOOD_SECTION_SETS':
                    case 'GENERAL_SECTION_SETS':
                    case 'CONCRETE_SECTION_SETS':
                    case 'MASONRY_SECTION_SETS':
                    case 'ALUMINUM_SECTION_SETS':
                    case 'STAINLESS_SECTION_SETS':
                        $matchs['a()'] = $this->setIntToCorrString($matchs, 'a', $this->memberTypes);
                        $matchs['b()'] = $this->setIntToCorrString($matchs, 'b', $this->materialTypes);
                        $matchs['c()'] = $this->setParsedSegmentIndex($matchs, 'c', $this->matFromSection[$subsegment], 'MATERIAL_LABEL', 0);
                        $matchs['mat()'] = $this->sectionToMatTypes[$subsegment] ?? '';
                        break;

                    case 'ALUMINUM_MATERIAL':
                        $matchs['al_table()'] = $this->setIntToCorrString($matchs, 'al_table', $this->alTables);
                        $matchs['mat()'] = $this->materialToMatTypes[$subsegment] ?? '';
                        break;
                    case 'WOOD_MATERIAL':
                        $matchs['wood_type()'] = $this->setIntToCorrString($matchs, 'wood_type', $this->woodTypes);
                        $matchs['wood_species()'] = $this->setIntToCorrString($matchs, 'wood_species', $this->woodSpecies);
                        $matchs['wood_grade()'] = $this->setIntToCorrString($matchs, 'wood_grade', $this->woodGrades);
                        $matchs['mat()'] = $this->materialToMatTypes[$subsegment] ?? '';
                        break;
                    case 'GENERAL_MATERIAL':
                    case 'HR_STEEL_MATERIAL':
                    case 'CF_STEEL_MATERIAL':
                    case 'MASONRY_MATERIAL':
                    case 'CONCRETE_MATERIAL':
                    case 'S_STEEL_MATERIAL':
                        $matchs['mat()'] = $this->materialToMatTypes[$subsegment] ?? '';
                        break;

                    case 'MEMBERS_MAIN_DATA':
                        $matchs['a()'] = $this->setParsedSegmentIndex($matchs, 'a', 'NODES', 'NODE_LABEL');
                        $matchs['b()'] = $this->setParsedSegmentIndex($matchs, 'b', 'NODES', 'NODE_LABEL');
                        $matchs['c()'] = $this->setParsedSegmentIndex($matchs, 'c', 'NODES', 'NODE_LABEL');
                        $matchs['e(i)'] = $this->setSectionforMMD($matchs);
                        $matchs['h(i)'] = $this->setHfromIforMMD($matchs);
                        break;

                    case 'MEMBERS_DESIGN_PARAMETERS':
                        $matchs['a()'] = $this->setIntToCorrString($matchs, 'a', $this->memberTypes);
                        break;
                }

                $matchs['_index'] = $index;
                $result[] = $matchs;
            } else if (trim($record) != "" and strpos(" " . trim($record), '[') != 1) {
                //$this->errors[] = "NOT PARSED: " . $record;
            }
        }
        return $result;
    }

    /**
     * Get Section by provided key.
     *
     * @param $key
     * @param string $text
     * @return null|string
     */
    protected function get_record_value($key, $text = "")
    {
        $text = $text ?: $this->file_text;

        $matchs = [];
        $pattern = sprintf('#\[\.*%1$s\](.*?)\[\.*END_%1$s]#s', $key);
        if (preg_match($pattern, $text, $matchs)) {
            if (trim($key) == "NOTIONALLoad_PARAMETERS") {
                $matchs[1] = preg_replace("#([0-9\.]+),#", "$1", $matchs[1]);
            }
            $matchs[1] = preg_replace("#<\d+>#", "", $matchs[1]);
            return trim($matchs[1]);
        } else {
            return NULL;
        }
    }

    /**
     * @param array $matchs
     * @return array
     */
    protected function clearNumericMatches(array $matchs)
    {
        $section_row = [];
        foreach ($matchs as $key => $value) {
            if (!is_int($key)) {
                //convert scientific format
                $section_row[$key] = $this->convertValue($value);
            }
        }
        return $section_row;
    }

    /**
     * @param $matchs
     * @return null|string
     */
    protected function setHfromIforMMD($matchs)
    {
        $h = (int)($matchs['h'] ?? null);
        $i = (int)($matchs['i'] ?? null);
        $res = null;

        switch ($h) {
            case 0:
                $items = $this->subsegment_parse('GENERAL_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'General Material';
                break;
            case 1:
                $items = $this->subsegment_parse('HR_STEEL_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Hot Rolled Steel';
                break;
            case 2:
                $items = $this->subsegment_parse('CF_STEEL_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Cold Formed Steel';
                break;
            case 3:
                $items = $this->subsegment_parse('WOOD_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Wood';
                break;
            case 4:
                $items = $this->subsegment_parse('MASONRY_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Masonry';
                break;
            case 5:
                $items = $this->subsegment_parse('CONCRETE_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Concrete';
                break;
            case 6:
                $items = $this->subsegment_parse('ALUMINUM_MATERIAL');
                $res = $items[$i]['MATERIAL_LABEL'] ?? 'Aluminium Materal Type';
                break;
            default:
                $res = null;
                break;
        }
        return $res;
    }

    /**
     * @param $value
     * @return float
     */
    protected function convertValue($value)
    {
        return is_numeric($value) ? (float)$value : $value;
    }

    /**
     * @param $matchs
     * @return null|string
     */
    protected function setSectionforMMD($matchs)
    {
        $h = (int)($matchs['h'] ?? null);
        $e = (int)($matchs['e'] ?? null);
        $direct = $matchs['SHAPE_LABEL'] ?? null;
        $res = null;

        switch ($h) {
            case 0:
                $items = [];
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 1:
                $items = $this->subsegment_parse('HR_STEEL_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 2:
                $items = $this->subsegment_parse('CF_STEEL_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 3:
                $items = $this->subsegment_parse('WOOD_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 4:
                $items = $this->subsegment_parse('GENERAL_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 5:
                $items = $this->subsegment_parse('CONCRETE_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 6:
                $items = $this->subsegment_parse('ALUMINUM_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            case 7:
                $items = $this->subsegment_parse('MASONRY_SECTION_SETS');
                $res = $items[$e]['SECTION_LABEL'] ?? $direct;
                break;
            default:
                $res = null;
                break;
        }
        return $res;
    }

    /**
     * @param array $matchs
     * @param string $val
     * @param string $subsegment
     * @param string $parsed_key
     * @param int $minus
     * @return null
     */
    protected function setParsedSegmentIndex(array $matchs, string $val, string $subsegment, string $parsed_key, int $minus = 1)
    {
        $el = isset($matchs[$val]) ? $matchs[$val] : false;
        $items = $this->subsegment_parse($subsegment);
        return is_numeric($el) ? ($items[$el-$minus][$parsed_key] ?? null) : null;
    }

    /**
     * @param $matchs
     * @param $key
     * @param $array
     * @return string|int|null
     */
    protected function setIntToCorrString($matchs, $key, $array)
    {
        $a = (int)($matchs[$key] ?? null);
        $res = null;
        return $array[$a] ?? $a;
    }

    /**
     * A lot of different parsing params.
     *
     * @param string $subsegment
     * @return string
     * @throws \Exception
     */
    protected function getParseParams(string $subsegment)
    {
        switch ($subsegment) {
            case 'VERSION_NUMBER':
            case 'PROGRAM_INFO':
            case 'DATA_INTEGRITY_KEY':
            case 'CLOUDID':
            case 'OS_VERSION':

            case 'MODEL_TITLE':
            case 'COMPANY_NAME':
            case 'DESIGNER_NAME':
            case 'JOB_NUMBER':
            case 'MODEL_NOTES':

            case 'FILE_SOURCE':
            case 'FILE_TYPE':
            case 'HORIZ_GRID':
            case 'VERT_GRID':
            case 'WOOD_BLOCKS':
            case 'WOOD_STRAPS':
                $options = "#(?P<VAL>.*) #";
                break;


            case 'UNITS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>..*?) #";
                break;


            case 'SOLUTION_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+)"
                    . "(?P<r> ..*)?#";
                break;


            case 'DESIGN_CODES':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>..*)#";
                break;


            case 'WIND_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+)"
                    . "(?P<k> ..*)?#";
                break;


            case 'IS_WIND_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>..*?) #";
                break;


            case 'MEXI_WIND_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n_nn>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>..*?) #";
                break;


            case 'NBC_2005_WIND_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>..*?) #";
                break;


            case 'SEISMIC_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n_nn>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>[-0-9+eE\.]+) +"
                    . "(?P<p_pp>[-0-9+eE\.]+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s_ss>[-0-9+eE\.]+) +"
                    . "(?P<t>-?\d+) +"
                    . "(?P<u_uu>[-0-9+eE\.]+) +"
                    . "(?P<v_vv>[-0-9+eE\.]+) +"
                    . "(?P<w_ww>[-0-9+eE\.]+) +"
                    . "(?P<x_xx>[-0-9+eE\.]+) +"
                    . "(?P<y_yy>[-0-9+eE\.]+) +"
                    . "(?P<z_zz>[-0-9+eE\.]+) +"
                    . "(?P<a1_a1a1>[-0-9+eE\.]+) +"
                    . "(?P<b1_b1b1>[-0-9+eE\.]+) +"
                    . "(?P<c1_c1c1>[-0-9+eE\.]+) +"
                    . "(?P<d1>-?\d+) +"
                    . "(?P<e1_e1e1>[-0-9+eE\.]+) +"
                    . "(?P<f1_f1f1>..*?) #";
                break;


            case 'SEISMIC_DETAILING':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>..*?) #";
                break;


            case 'IS_SEISMIC_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+)"
                    . "(?P<k> ..*)?#";
                break;


            case 'MEXICAN_SEISMIC_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>..*?) #";
                break;


            case 'NBC_2005_SEISMIC_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+)+"
                    . "(?P<l> ..*)?#";
                break;


            case 'NOTIONALLoad_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>..*?) #";
                break;


            case 'CONCRETE_PARAMETERS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l>[-0-9+eE\.]+) +"
                    . "(?P<m>[-0-9+eE\.]+) +"
                    . "(?P<n>[-0-9+eE\.]+) +"
                    . "(?P<o>[-0-9+eE\.]+) +"
                    . "(?P<p>..*?) #";
                break;


            case 'FOOTING_PARAMETERS':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>..*?) #";
                break;


            case 'LC_GENERATOR_RLL_OPTIONS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>..*?) #";
                break;


            case 'LABEL_LENGTHS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s>-?\d+) +"
                    . "(?P<t>-?\d+) +"
                    . "(?P<u>-?\d+) +"
                    . "(?P<v>-?\d+) +"
                    . "(?P<w>..*?) #";
                break;


            case 'GENERAL_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>..*?) #";
                break;


            case 'HR_STEEL_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<str_yield>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>-?\d+) +"
                    . "(?P<hot_roll_ry>[-0-9+eE\.]+) +"
                    . "(?P<str_ultimate>[-0-9+eE\.]+) +"
                    . "(?P<hot_roll_rt>..*?) #";
                break;


            case 'CF_STEEL_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<str_yield>[-0-9+eE\.]+) +"
                    . "(?P<str_ultimate>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>..*?) #";
                break;


            case 'ALUMINUM_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<al_table>-?\d+) +"
                    . "(?P<al_kt>[-0-9+eE\.]+) +"
                    . "(?P<al_ftu>[-0-9+eE\.]+) +"
                    . "(?P<al_fty>[-0-9+eE\.]+) +"
                    . "(?P<al_fcy>[-0-9+eE\.]+) +"
                    . "(?P<al_fsu>[-0-9+eE\.]+) +"
                    . "(?P<al_ct>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>..*?) #";
                break;


            case 'WOOD_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "\"(?P<wood_species_label>.*?) *\" +"
                    . "(?P<wood_type>-?\d+) +"
                    . "(?P<wood_species>-?\d+) +"
                    . "(?P<wood_grade>-?\d+) +"
                    . "(?P<wood_cm>-?\d+) +"
                    . "(?P<wood_e_mod>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "\"(?P<bim_id>..*? )\"#";
                break;


            case 'MASONRY_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<masonry_fpm>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>-?\d+) +"
                    . "(?P<masonry_panel>-?\d+) +"
                    . "(?P<masonry_block>-?\d+) +"
                    . "(?P<masonry_grout>-?\d+) +"
                    . "(?P<flex_steel>[-0-9+eE\.]+) +"
                    . "(?P<shear_steel>..*?) #";
                break;


            case 'CONCRETE_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<concrete_fpc>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>[-0-9+eE\.]+) +"
                    . "(?P<concrete_lambda>[-0-9+eE\.]+) +"
                    . "(?P<flex_steel>[-0-9+eE\.]+) +"
                    . "(?P<shear_steel>..*?) #";
                break;


            case 'S_STEEL_MATERIAL':
                $options = "#"
                    . "\"(?P<MATERIAL_LABEL>.*?) *\" +"
                    . "(?P<modulus_elastic>[-0-9+eE\.]+) +"
                    . "(?P<modulus_shear>[-0-9+eE\.]+) +"
                    . "(?P<poisson_ratio>[-0-9+eE\.]+) +"
                    . "(?P<thermal_coef>[-0-9+eE\.]+) +"
                    . "(?P<density>[-0-9+eE\.]+) +"
                    . "(?P<str_yield>[-0-9+eE\.]+) +"
                    . "(?P<bim_id>..*) +"
                    . "(?P<str_ultimate>[-0-9+eE\.]+) +"
                    . "(?P<last>..*?) #";
                break;


            case 'CUSTOM_WOOD_PROPERTIES':
                $options = "#"
                    . "\"(?P<CUST_SPECIES_LABEL>.*?) *\" +"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>..*?) #";
                break;


            case 'HR_STEEL_SECTION_SETS':
            case 'CF_STEEL_SECTION_SETS':
            case 'WOOD_SECTION_SETS':
            case 'GENERAL_SECTION_SETS':
            case 'CONCRETE_SECTION_SETS':
            case 'MASONRY_SECTION_SETS':
            case 'ALUMINUM_SECTION_SETS':
            case 'STAINLESS_SECTION_SETS':
                $options = "#"
                    . "\"(?P<SECTION_LABEL>.*?) *\" +"
                    . "\"(?P<DESIGN_LIST>.*?) *\" +"
                    . "\"(?P<SHAPE_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>..*?) #";
                break;


            case 'WOOD_SCHEDULE':
                $options = "#"
                    . "\"(?P<WOOD_CODE>.*?)\" +"
                    . "\"(?P<PANEL_GROUP>.*?)\" +"
                    . "\"(?P<LABEL>.*?)\" +"
                    . "\"(?P<GRADE>.*?)\" +"
                    . "\"(?P<NAIL_SIZE>.*?)\" +"
                    . "\"(?P<STAPLE_SIZE>.*?)\" +"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_aa>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "((?P<g_gg>[-0-9+eE\.]+))"
                    . "(?P<h> [-0-9+eE\.]+)? #";
                break;


            case 'WOOD_HOLDDOWN_SERIES':
                $options = "#"
                    . "\"(?P<MANUFACTURER>.*?) *\" +"
                    . "\"(?P<LABEL>.*?) *\" +"
                    . "\"(?P<MANUFACURER>.*?) *\" +"
                    . "\"(?P<BOLT_SIZE>.*?) *\" +"
                    . "\"(?P<NAIL_SIZE>.*?) *\" +"
                    . "\"(?P<LOAD_TYPE>.*?) *\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+)+"
                    . "(?P<i_ii> ..*)? #";
                break;


            case 'SIZE_UC_RULES':
                $options = "#"
                    . "\"(?P<REDESIGN_LABEL>.*?) *\" +"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>..*?) #";
                break;


            case 'DEFLECTION_RULES':
                $options = "#"
                    . "\"(?P<REDESIGN_LABEL>.*?) *\" +"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>..*?) #";
                break;


            case 'REBAR_RULES':
                $options = "#"
                    . "\"(?P<REDESIGN_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>..*?) #";
                break;


            case 'MASONRY_WALLPANEL_RULES':
                $options = "#"
                    . "\"(?P<LABEL>..*?)\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+)+"
                    . "(?P<h> ..*)#";
                break;


            case 'WOOD_WALLPANEL_RULES':
                $options = "#"
                    . "\"(?P<LABEL1>.*?) *\" +"
                    . "\"(?P<TOP_PLATE>.*?) *\" +"
                    . "\"(?P<SILL_PLATE>.*?) *\" +"
                    . "\"(?P<STUDS>.*?) *\" +"
                    . "\"(?P<BOTTOM_PLATE>.*?) *\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>[-0-9+eE\.]+) +"
                    . "\"(?P<HOLD_DOWN_SERIES>.*?) *\" +"
                    . "\"(?P<MANUFACTURER>.*?) *\" +"
                    . "(?P<k>[-0-9+eE\.]+) +"
                    . "\"(?P<LABEL2>.*?) *\" +"
                    . "\"(?P<MANUFACURER>.*?) *\" +"
                    . "\"(?P<BOLT_SIZE>.*?) *\" +"
                    . "\"(?P<NAIL_SIZE>.*?) *\" +"
                    . "\"(?P<LOAD_TYPE>.*?) *\" +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n>[-0-9+eE\.]+) +"
                    . "(?P<o>[-0-9+eE\.]+) +"
                    . "(?P<p>[-0-9+eE\.]+) +"
                    . "(?P<q_qq>[-0-9+eE\.]+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s_ss>[-0-9+eE\.]+) +"
                    . "(?P<t_tt>[-0-9+eE\.]+) +"
                    . "(\"(?P<HEADER_SIZE>.*?)\" +)?"
                    . "((?P<u_uu>[-0-9+eE\.]+) +)?"
                    . "((?P<v_vv>[-0-9+eE\.]+) +)?"
                    . "((?P<w>[-0-9+eE\.]+) +)?"
                    . "((?P<x_xx>[-0-9+eE\.]+) +)?"
                    . "(?P<y>..*)?#";
                break;


            case 'CONCRETE_WALLPANEL_RULES':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>..*?) #";
                break;


            case 'UC_WALLPANEL_RULES':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>..*?) #";
                break;


            case 'MEMBER_SIZE_UC_RULES':
                $options = "#"
                    . "\"(?P<REDESIGN_LABEL>.*?) *\" +"
                    . "(?P<a_aa>[-0-9+e\.]+) +"
                    . "(?P<b_bb>[-0-9+e\.]+) +"
                    . "(?P<c_cc>[-0-9+e\.]+) +"
                    . "(?P<d_dd>[-0-9+e\.]+) +"
                    . "(?P<e_ee>[-0-9+e\.]+) +"
                    . "(?P<f_ff>..*?) #";
                break;


            case 'MEMBER_DEFLECTION_RULES':
                $options = "#"
                    . "\"(?P<REDESIGN_LABEL>..*?)\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "((?P<g>[-0-9+eE\.]+) +)?"
                    . "((?P<h>[-0-9+eE\.]+) +)?"
                    . "((?P<i>[-0-9+eE\.]+) +)?"
                    . "((?P<j>[-0-9+eE\.]+) +)?"
                    . "((?P<k>[-0-9+eE\.]+) +)?"
                    . "((?P<l>[-0-9+eE\.]+) +)?"
                    . "((?P<m>[-0-9+eE\.]+) +)?"
                    . "((?P<n>[-0-9+eE\.]+) +)?"
                    . "((?P<o>[-0-9+eE\.]+) +)?"
                    . "((?P<p>[-0-9+eE\.]+) +)?"
                    . "(?P<q>..*)?#";
                break;


            case 'MEMBER_REBAR_RULES':
                $options = "#"
                    . "\"(?P<LABEL>..*?)\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>[-0-9+eE\.]+) +"
                    . "(?P<k>[-0-9+eE\.]+) +"
                    . "(?P<l>[-0-9+eE\.]+) +"
                    . "(?P<m>[-0-9+eE\.]+) +"
                    . "(?P<n>[-0-9+eE\.]+) +"
                    . "(?P<o>[-0-9+eE\.]+)"
                    . "(?P<p> ..*)#";
                break;


            case 'SEISMIC_DESIGN_RULES':
                $options = "#"
                    . "\"(?P<SEISMIC_DESIGN_RULES_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>..*?) #";
                break;


            case 'CONNECTION_RULES':
                $options = "#"
                    . "\"(?P<CONNECTION_RULES_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>..*?) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>..*?) #";
                break;


            case 'NODES':
                $options = "#"
                    . "\"(?P<NODE_LABEL>.*?) *\" +"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>..*?) #";
                break;


            case 'BOUNDARY_CONDITIONS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>[-0-9+eE\.]+) +"
                    . "(?P<p>[-0-9+eE\.]+) +"
                    . "(?P<q>[-0-9+eE\.]+) +"
                    . "(?P<r>[-0-9+eE\.]+) +"
                    . "(?P<s>[-0-9+eE\.]+) +"
                    . "(?P<t>[-0-9+eE\.]+) +"
                    . "(?P<u>[-0-9+eE\.]+) +"
                    . "(?P<v>[-0-9+eE\.]+) +"
                    . "(?P<w>..*?) #";
                break;


            case 'DIAPHRAGMS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>..*?) #";
                break;


            case 'DRIFT_DEFS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>..*?) #";
                break;


            case 'SHAPES_LIST':
                $options = "#"
                    . "\"(?P<SHAPE_NAME>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<p0>..*?) +"
                    . "(?P<p1>..*?) +"
                    . "(?P<p2>..*?) +"
                    . "(?P<p3>..*?) +"
                    . "(?P<p4>..*?) +"
                    . "(?P<p5>..*?) +"
                    . "(?P<p6>..*?) +"
                    . "(?P<p7>..*?) +"
                    . "(?P<p8>..*?) +"
                    . "(?P<p9>..*?) +"
                    . "(?P<p10>..*?) +"
                    . "(?P<p11>..*?) +"
                    . "(?P<p12>..*?) +"
                    . "(?P<p13>..*?) +"
                    . "(?P<p14>..*?) +"
                    . "(?P<p15>..*?) +"
                    . "(?P<p16>..*?) +"
                    . "(?P<p17>..*?) +"
                    . "(?P<p18>..*?) +"
                    . "(?P<p19>..*?) +"
                    . "(?P<p20>..*?) +"
                    . "(?P<p21>..*?) +"
                    . "(?P<p22>..*?) +"
                    . "(?P<p23>..*?) #";
                break;


            case 'CUSTOM_REPORT_SECTIONS_3D':
                $options = "#"
                    . "\"(?P<a>..*?)\" "
                    . "\"(?P<b>..*?)\" "
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>..*?) #";
                break;


            case 'MEMBERS_MAIN_DATA':
                $options = "#"
                    . "\"(?P<MEMBER_LABEL>.*?) *\" +"
                    . "\"(?P<DESIGN_LIST>.*?) *\" +"
                    . "\"(?P<SHAPE_LABEL>.*?) *\" +"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>[-0-9+eE\.]+) +"
                    . "(?P<k>[-0-9+eE\.]+) +"
                    . "(?P<l>[-0-9+eE\.]+) +"
                    . "(?P<m>[-0-9+eE\.]+) +"
                    . "(?P<o>[-0-9+eE\.]+) +"
                    . "(?P<p>[-0-9+eE\.]+) +"
                    . "(?P<q>[-0-9+eE\.]+) +"
                    . "(?P<r>[-0-9+eE\.]+) +"
                    . "(?P<s>[-0-9+eE\.]+) +"
                    . "(?P<t>[-0-9+eE\.]+) +"
                    . "(?P<u>[-0-9+eE\.]+) +"
                    . "(?P<v>..*?) #";
                break;


            case 'MEMBERS_SUPPLEMENTARY_DATA':
                $options = "#"
                    . "\"(?P<label>..*?)\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>..*?) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>..*?) +"
                    . "(?P<i>..*?) #";
                break;


            case 'MEMBERS_DESIGN_PARAMETERS':
                $options = "#"
                    . "\"(?P<MEMBER_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_b>[-0-9+eE\.]+) +"
                    . "(?P<c_c>[-0-9+eE\.]+) +"
                    . "(?P<d_d>[-0-9+eE\.]+) +"
                    . "(?P<e_e>[-0-9+eE\.]+) +"
                    . "(?P<f_f>[-0-9+eE\.]+) +"
                    . "(?P<g_g>[-0-9+eE\.]+) +"
                    . "(?P<h_h>[-0-9+eE\.]+) +"
                    . "(?P<i_i>[-0-9+eE\.]+) +"
                    . "(?P<j_j>[-0-9+eE\.]+) +"
                    . "(?P<k_k>[-0-9+eE\.]+) +"
                    . "(?P<l_l>[-0-9+eE\.]+) +"
                    . "(?P<m_m>[-0-9+eE\.]+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r_r>[-0-9+eE\.]+) +"
                    . "(?P<s_s>[-0-9+eE\.]+) +"
                    . "(?P<t_t>[-0-9+eE\.]+) +"
                    . "(?P<u_u>[-0-9+eE\.]+) +"
                    . "(?P<v_v>[-0-9+eE\.]+) +"
                    . "(?P<w_w>[-0-9+eE\.]+) +"
                    . "\"(?P<x1>.*?)\" +"
                    . "\"(?P<x2>.*?)\" +"
                    . "(?P<x3>[-0-9+eE\.]+) +"
                    . "(?P<x4>[-0-9+eE\.]+) +"
                    . "(?P<x5>[-0-9+eE\.]+)+"
                    . "(?P<x6> ..*)#";
                break;


            case 'MEMBERS_DETAILING_DATA':
                $options = "#"
                    . "\"(?P<MEMBER_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>..*?) #";
                break;


            case 'PLATES':
                $options = "#"
                    . "\"(?P<PLATE_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>[-0-9+eE\.]+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>..*?) #";
                break;


            case 'WALLPANEL_GENERAL':
                $options = "#"
                    . "\"(?P<WALLPANEL_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o_oo>[-0-9+eE\.]+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s>-?\d+) +"
                    . "(?P<t_tt>[-0-9+eE\.]+) +"
                    . "(?P<u_uu>[-0-9+eE\.]+) +"
                    . "(?P<v>-?\d+) +"
                    . "(?P<w>-?\d+) +"
                    . "(?P<x_xx>[-0-9+eE\.]+) +"
                    . "(?P<y_yy>[-0-9+eE\.]+) +"
                    . "(?P<z_zz>..*?) #";
                break;


            case 'WALLPANEL_NODES':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>..*?) #";
                break;


            case 'WALLPANEL_REGIONS':
                $options = "#"
                    . "\"(?P<REGION_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p_pp>[-0-9+eE\.]+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s>-?\d+) +"
                    . "(?P<t>-?\d+) +"
                    . "(?P<u_uu>[-0-9+eE\.]+) +"
                    . "(?P<v>-?\d+) +"
                    . "(?P<w>..*?) #";
                break;


            case 'WALLPANEL_REGIONS_NODES':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>..*?) #";
                break;


            case 'WALLPANEL_REGIONS_INACTIVE':
            case 'WALLPANEL_REGIONS_NODES_INACTIVE':
            case 'WALLPANEL_OPENINGS_INACTIVE':
            case 'WALLPANEL_OPENINGS_ADDL_INFO_INACTIVE':
            case 'WALLPANEL_LINTELS_INACTIVE':
            case 'WALLPANEL_DETACH_DIAPH':
                $options = "#"
                    . "(?P<a>..*)#";
                break;


            case 'WALLPANEL_OPENINGS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n_nn>[-0-9+eE\.]+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>..*?) #";
                break;


            case 'WALLPANEL_LINTELS':
                $options = "#"
                    . "\"(?P<LINTEL_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p_pp>[-0-9+eE\.]+) +"
                    . "(?P<q_qq>[-0-9+eE\.]+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s>-?\d+) +"
                    . "(?P<t>-?\d+) +"
                    . "(?P<u_uu>[-0-9+eE\.]+) +"
                    . "(?P<v_vv>[-0-9+eE\.]+) +"
                    . "(?P<w>-?\d+) +"
                    . "(?P<x_xx>[-0-9+eE\.]+) +"
                    . "(?P<y_yy>[-0-9+eE\.]+) +"
                    . "(?P<z>-?\d+) +"
                    . "(?P<a1>-?\d+) +"
                    . "(?P<b1:>..*?) #";
                break;


            case 'WALLPANEL_BCS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p_pp>[-0-9+eE\.]+) +"
                    . "(?P<q_qq>[-0-9+eE\.]+) +"
                    . "(?P<r_rr>[-0-9+eE\.]+) +"
                    . "(?P<s_ss>[-0-9+eE\.]+) +"
                    . "(?P<t_tt>[-0-9+eE\.]+) +"
                    . "(?P<u_uu>[-0-9+eE\.]+) +"
                    . "(?P<v>-?\d+) +"
                    . "(?P<w>..*?) #";
                break;


            case 'WALLPANEL_RELEASE':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>-?\d+) +"
                    . "(?P<m>-?\d+) +"
                    . "(?P<n>-?\d+) +"
                    . "(?P<o>-?\d+) +"
                    . "(?P<p>-?\d+) +"
                    . "(?P<q>-?\d+) +"
                    . "(?P<r>-?\d+) +"
                    . "(?P<s>-?\d+) +"
                    . "(?P<t>-?\d+) +"
                    . "(?P<u>..*?) #";
                break;


            case 'FOOTING_DATA':
                $options = "#"
                    . "\"(?P<FOOTING_LABEL>.*?) *\" +"
                    . "(?P<a_a>[-0-9+eE\.]+) +"
                    . "(?P<b_b>[-0-9+eE\.]+) +"
                    . "(?P<c_c>[-0-9+eE\.]+) +"
                    . "(?P<d_d>[-0-9+eE\.]+) +"
                    . "(?P<e_e>[-0-9+eE\.]+) +"
                    . "(?P<f_f>[-0-9+eE\.]+) +"
                    . "(?P<g_g>[-0-9+eE\.]+) +"
                    . "(?P<h_h>[-0-9+eE\.]+) +"
                    . "(?P<j_j>[-0-9+eE\.]+) +"
                    . "(?P<k_k>[-0-9+eE\.]+) +"
                    . "(?P<l_l>[-0-9+eE\.]+) +"
                    . "(?P<m_m>[-0-9+eE\.]+) +"
                    . "(?P<o_o>[-0-9+eE\.]+) +"
                    . "(?P<p_p>[-0-9+eE\.]+) +"
                    . "(?P<q_q>[-0-9+eE\.]+) +"
                    . "(?P<r_r>[-0-9+eE\.]+) +"
                    . "(?P<s_s>[-0-9+eE\.]+) +"
                    . "(?P<t_t>[-0-9+eE\.]+) +"
                    . "(?P<u_u>[-0-9+eE\.]+) +"
                    . "(?P<v_v>[-0-9+eE\.]+) +"
                    . "(?P<w_w>[-0-9+eE\.]+) +"
                    . "(?P<x>-?\d+) +"
                    . "(?P<y>-?\d+) +"
                    . "(?P<z>-?\d+) +"
                    . "(?P<a1>-?\d+) +"
                    . "(?P<b1>-?\d+) +"
                    . "(?P<c1>..*?) #";
                break;


            case 'BASIC_LOAD_CASES':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "\"(?P<BASIC_LOAD_CASE_LABEL>.*?)\" +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+)+"
                    . "(?P<l> ..*)?#";
                break;


            case 'NODE_LOADS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>..*?) #";
                break;


            case 'POINT_LOADS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>..*?) #";
                break;


            case 'DIRECT_DISTRIBUTED_LOADS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k>[-0-9+eE\.]+) +"
                    . "(?P<l>[-0-9+eE\.]+) +"
                    . "(?P<m>[-0-9+eE\.]+) +"
                    . "(?P<n>..*?) #";
                break;


            case 'AREA_LOADS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>..*?) #";
                break;


            case 'SURFACE_LOADS':
                $options = "#"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n_nn>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>..*?) #";
                break;


            case 'MOVING_LOADS':
                $options = "#"
                    . "\"(?P<MOVING_LOAD_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f>-?\d+) +"
                    . "(?P<g>-?\d+) +"
                    . "(?P<h>-?\d+) +"
                    . "(?P<i>-?\d+) +"
                    . "(?P<j>-?\d+) +"
                    . "(?P<k>-?\d+) +"
                    . "(?P<l>..*?) #";
                break;


            case 'TIME_HISTORY_INPUT':
                $options = "#"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>..*?) #";
                break;


            case 'TIME_HISTORY_LOADS':
                $options = "#"
                    . "(?P<a>..*)#";
                break;


            case 'TIME_HISTORY_LOAD_GENERAL':
                $options = "#"
                    . "\"(?P<TIME_HISTORY_LOAD_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b_bb>..*?) #";
                break;


            case 'TIME_HISTORY_LOAD_ENTRIES':
                $options = "#"
                    . "\"(?P<TIME_HISTORY_LOAD_FUNCTION>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e_ee>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>..*?) #";
                break;


            case 'EIGENSOLUTION_DATA':
                $options = "#"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j>[-0-9+eE\.]+) +"
                    . "(?P<k>[-0-9+eE\.]+) +"
                    . "(?P<l>[-0-9+eE\.]+) +"
                    . "(?P<m>[-0-9+eE\.]+) +"
                    . "(?P<n>[-0-9+eE\.]+) +"
                    . "(?P<o>[-0-9+eE\.]+) +"
                    . "(?P<p>[-0-9+eE\.]+) +"
                    . "(?P<q>[-0-9+eE\.]+) +"
                    . "(?P<r>[-0-9+eE\.]+) +"
                    . "(?P<s>[-0-9+eE\.]+) +"
                    . "(?P<t>[-0-9+eE\.]+) +"
                    . "(?P<u>[-0-9+eE\.]+) +"
                    . "(?P<v>[-0-9+eE\.]+) +"
                    . "(?P<w>[-0-9+eE\.]+)"
                    . "(?P<x> ..*)#";
                break;


            case 'RESPONSE_SPECTRA_DATA':
                $options = "#"
                    . "(?P<a>[-0-9+eE\.]+) +"
                    . "(?P<b>[-0-9+eE\.]+) +"
                    . "(?P<c>[-0-9+eE\.]+) +"
                    . "(?P<d>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f>[-0-9+eE\.]+) +"
                    . "(?P<g>[-0-9+eE\.]+) +"
                    . "(?P<h>[-0-9+eE\.]+) +"
                    . "(?P<i>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+)"
                    . "(?P<l_ll> ..*)#";
                break;


            case 'SPECTRA_SCALING_FACTOR':
                $options = "#"
                    . "(?P<a_aa>[-0-9+eE\.]+) +"
                    . "(?P<b_bb>[-0-9+eE\.]+) +"
                    . "(?P<c_cc>[-0-9+eE\.]+) +"
                    . "(?P<d_dd>[-0-9+eE\.]+) +"
                    . "(?P<e>[-0-9+eE\.]+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "(?P<i_ii>[-0-9+eE\.]+) +"
                    . "(?P<j_jj>[-0-9+eE\.]+) +"
                    . "(?P<k_kk>[-0-9+eE\.]+) +"
                    . "(?P<l_ll>[-0-9+eE\.]+) +"
                    . "(?P<m_mm>[-0-9+eE\.]+) +"
                    . "(?P<n_nn>[-0-9+eE\.]+) +"
                    . "(?P<o_oo>[-0-9+eE\.]+) +"
                    . "(?P<p_pp>[-0-9+eE\.]+)"
                    . "(?P<q_qq> ..*)#";
                break;


            case 'LOAD_COMBINATIONS':
                $options = "#"
                    . "\"(?P<LOAD_COMB_LABEL>.*?) *\" +"
                    . "(?P<a>-?\d+) +"
                    . "(?P<b>-?\d+) +"
                    . "(?P<c>-?\d+) +"
                    . "(?P<d>-?\d+) +"
                    . "(?P<e>-?\d+) +"
                    . "(?P<f_ff>[-0-9+eE\.]+) +"
                    . "(?P<g_gg>[-0-9+eE\.]+) +"
                    . "(?P<h_hh>[-0-9+eE\.]+) +"
                    . "((?P<i>-?\d+) +)?"
                    . "((?P<j>-?\d+) +)?"
                    . "((?P<k>-?\d+) +)?"
                    . "((?P<l>-?\d+) +)?"
                    . "((?P<m>-?\d+) +)?"
                    . "((?P<o>-?\d+) +)?"
                    . "((?P<p>-?\d+) +)?"
                    . "((?P<q>-?\d+) +)?"
                    . "((?P<r>-?\d+) +)?"
                    . "((?P<s>-?\d+) +)?"
                    . "((?P<t>-?\d+) +)?"
                    . "((?P<u>-?\d+) +)?"
                    . "((?P<v>-?\d+) +)?"
                    . "((?P<w>-?\d+) +)?"
                    . "((?P<x>-?\d+) +)?"
                    . "((?P<y>-?\d+) +)?"
                    . "((?P<z>-?\d+) +)?"
                    . "\"(?P<blc1>.*?) *\" +"
                    . "(?P<s_ss1>[-0-9+eE\.]+) +"
                    . "\"(?P<blc2>.*?) *\" +"
                    . "(?P<s_ss2>[-0-9+eE\.]+) +"
                    . "\"(?P<blc3>.*?) *\" +"
                    . "(?P<s_ss3>[-0-9+eE\.]+) +"
                    . "\"(?P<blc4>.*?) *\" +"
                    . "(?P<s_ss4>[-0-9+eE\.]+) +"
                    . "\"(?P<blc5>.*?) *\" +"
                    . "(?P<s_ss5>[-0-9+eE\.]+) +"
                    . "\"(?P<blc6>.*?) *\" +"
                    . "(?P<s_ss6>[-0-9+eE\.]+) +"
                    . "\"(?P<blc7>.*?) *\" +"
                    . "(?P<s_ss7>[-0-9+eE\.]+) +"
                    . "\"(?P<blc8>.*?) *\" +"
                    . "(?P<s_ss8>[-0-9+eE\.]+) +"
                    . "\"(?P<blc9>.*?) *\" +"
                    . "(?P<s_ss9>[-0-9+eE\.]+) +"
                    . "\"(?P<blc10>.*?) *\" +"
                    . "(?P<s_ss10>..*?) #";
                break;


            default:
                throw new \Exception('Risa3D Parser :: Incorrect Risa3D Subsegment ('.$subsegment.').');
        }
        return $options;
    }
}