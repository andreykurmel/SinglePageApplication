<?php


namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Vanguard\Classes\DurationConvert;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Support\FileHelper;

class FormulaFuncService
{
    /**
     * @var array
     */
    public static $dcr_rows_linked = [];
    /**
     * @var array
     */
    public static $currentField = [];
    /**
     * @var array
     */
    public static $currentRow = [];
    /**
     * @var array
     */
    public static $afterUpdates = [];
    /**
     * @var null|Table
     */
    public static $metaTable = null;
    /**
     * @var null|int
     */
    public static $userId = null;

    /**
     * @var array
     */
    protected static $cacher = [];

    /**
     * HelperService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $value
     * @return mixed|string
     */
    protected static function val($value)
    {
        //remove '0000-00-00'
        if ($value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            $value = '';
        }
        return $value;
    }

    /**
     * Get Count Rows in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlCount($ref_cond_str, $field)
    {
        return self::sqlAgregate('count', $ref_cond_str, $field);
    }

    /**
     * Get Count Unique Rows in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlCountUnique($ref_cond_str, $field)
    {
        return self::sqlAgregate('countunique', $ref_cond_str, $field);
    }

    /**
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    protected static function sqlAgregate($type, $ref_cond_str, $field)
    {
        /*$ref_cond_id = [];
        preg_match('/\\[(.*)\\]/i', $ref_cond_str, $ref_cond_id);
        $ref_cond_id = $ref_cond_id[1] ?? null;*/

        //empty required fields
        if (!$ref_cond_str || !$field) {
            return null;
        }
        $ref_cond_str = preg_replace('/\s/i', '', $ref_cond_str);

        $sqlData = self::getSqlTable($ref_cond_str);
        $table = $sqlData['table'];
        $refCondition = $sqlData['refCond'];

        if (!$table) {
            return null;
        }

        //get db_name from Field
        $db_field = $table->_fields->filter(function($el) use ($field) {
            return preg_replace('/[^\p{L}\d]/u', '', $el->name) == preg_replace('/[^\p{L}\d]/u', '', $field);
        })->first();
        if (!$db_field) {
            return null;
        }
        //-------

        //Use not-saved dcr linked rows to calculate totals before the saving in DB
        if (self::$dcr_rows_linked) {
            $rows = self::$dcr_rows_linked[$table->id] ?? [];
            $rows = Arr::pluck($rows, $db_field->field) ?? [];
            if ($rows) {
                return self::useArrayFunc($type, $rows);
            }
        }

        //calc formula
        $res = null;
        try {
            $sql = new TableDataQuery($table);
            if ($refCondition) {
                $sql->applyRefConditionRow($refCondition, self::$currentRow, false);
            }
            $sql = $sql->getQuery();

            switch ($type) {
                case 'sum':
                    $res = $sql->sum($db_field->field);
                    break;
                case 'min':
                    $res = $sql->min($db_field->field);
                    break;
                case 'max':
                    $res = $sql->max($db_field->field);
                    break;
                case 'avg':
                    $res = $sql->avg($db_field->field);
                    break;
                case 'mean':
                    $res = ( $sql->min($db_field->field) + $sql->max($db_field->field) ) / 2;
                    break;
                case 'var':
                    $res = $sql->selectRaw("variance($db_field->field) as `res`")->get();
                    $res = $res[0]['res'] ?? 0;
                    break;
                case 'std':
                    $res = $sql->selectRaw("std($db_field->field) as `res`")->get();
                    $res = $res[0]['res'] ?? 0;
                    break;
                case 'countunique':
                    $res = $sql->distinct()->count($db_field->field);
                    break;
                default:
                    $res = $sql->count($db_field->field);
                    break;
            }
        } catch (\Exception $e) {
        }

        return floatval($res);
    }

    /**
     * @param string $ref_cond_str
     * @return array
     */
    protected static function getSqlTable(string $ref_cond_str): array
    {
        $table = null;
        $refCondition = null;

        if ($ref_cond_str == '$this') {
            $table = self::$metaTable;
        } else {
            //get Ref Condtion
            if (self::$metaTable && self::$metaTable->_ref_conditions) {
                foreach (self::$metaTable->_ref_conditions as $ref_cond) {
                    if (preg_replace('/\s/i', '', $ref_cond->name) == $ref_cond_str) {
                        $refCondition = $ref_cond;
                    }
                }
            }
            $table = $refCondition ? $refCondition->_ref_table : null;
        }

        return [
            'table' => $table,
            'refCond' => $refCondition,
        ];
    }

    /**
     * @param $type
     * @param $rows
     * @return float|int
     */
    protected static function useArrayFunc($type, $rows): float|int
    {
        return match ($type) {
            'sum' => self::arraySum($rows),
            'min' => self::arrayMin($rows),
            'max' => self::arrayMax($rows),
            'avg' => self::arrayAvg($rows),
            'mean' => self::arrayMean($rows),
            'var' => self::arrayVariance($rows),
            'std' => self::arrayStd($rows),
            'countunique' => self::arrayCountUnique($rows),
            default => self::arrayCount($rows),
        };
    }

    /**
     * Get Sum Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlSum($ref_cond_str, $field)
    {
        return self::sqlAgregate('sum', $ref_cond_str, $field);
    }

    /**
     * Get Average Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlAvg($ref_cond_str, $field)
    {
        return self::sqlAgregate('avg', $ref_cond_str, $field);
    }

    /**
     * Get Mean Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlMean($ref_cond_str, $field)
    {
        return self::sqlAgregate('mean', $ref_cond_str, $field);
    }

    /**
     * Get Min Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlMin($ref_cond_str, $field)
    {
        return self::sqlAgregate('min', $ref_cond_str, $field);
    }

    /**
     * Get Max Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlMax($ref_cond_str, $field)
    {
        return self::sqlAgregate('max', $ref_cond_str, $field);
    }

    /**
     * Get Variance of Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlVariance($ref_cond_str, $field)
    {
        return self::sqlAgregate('var', $ref_cond_str, $field);
    }

    /**
     * Get STD of Field in Table.
     *
     * @param $ref_cond_str
     * @param $field
     * @return null|string
     */
    public static function sqlStd($ref_cond_str, $field)
    {
        return self::sqlAgregate('std', $ref_cond_str, $field);
    }

    /**
     * Get Sum of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayCount($fields_array)
    {
        return count($fields_array);
    }

    /**
     * Get Sum of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayCountUnique($fields_array)
    {
        return count(array_unique($fields_array));
    }

    /**
     * Get Sum of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arraySum($fields_array)
    {
        return array_sum($fields_array);
    }

    /**
     * Get Average of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayAvg($fields_array)
    {
        return self::arraySum($fields_array) / count($fields_array);
    }

    /**
     * Get Mean of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayMean($fields_array)
    {
        return (self::arrayMin($fields_array) + self::arrayMax($fields_array)) / 2;
    }

    /**
     * Get Min of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayMin($fields_array)
    {
        return min($fields_array);
    }

    /**
     * Get Max of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayMax($fields_array)
    {
        return max($fields_array);
    }

    /**
     * Get Variance of of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayVariance($fields_array)
    {
        $avg = self::arrayAvg($fields_array);
        $result = 0;
        foreach ($fields_array as $el) {
            $result += pow($avg-$el, 2);
        }
        return $result / count($fields_array);
    }

    /**
     * Get STD of of Array.
     *
     * @param $fields_array
     * @return float|int
     */
    public static function arrayStd($fields_array)
    {
        return sqrt( self::arrayVariance($fields_array) );
    }

    /**
     * @param $field
     * @param null $val1
     * @param null $val2
     * @return bool
     */
    public static function isEmpty($field, $val1 = null, $val2 = null)
    {
        return !self::val($field)
            ? ($val1 === null ? true : $val1)
            : ($val2 === null ? false : $val2);
    }

    /**
     * Value already is changed, so it is just a wrapper
     * @param $field
     * @return mixed|string
     */
    public static function ddloption($field)
    {
        return self::val($field);
    }

    /**
     * @param $condition
     * @param $if_true
     * @param null $if_false
     * @return null
     */
    public static function ifCondition($condition, $if_true, $if_false = null)
    {
        //Calc condition
        $avail_operators = ['==', '!=', '<=', '>=', '<>', '<', '>'];
        foreach ($avail_operators as $operator) {
            if (strpos($condition, $operator) !== false) {
                $arr = explode($operator, $condition);
                foreach ($arr as $i => $val) {
                    //convert Duration
                    if (DurationConvert::isDuration($val)) {
                        $arr[$i] = DurationConvert::duration2second($arr[$i]);
                    }
                }

                switch ($operator) {
                    case '<>':
                    case '!=': $condition = floatval($arr[0]) != floatval($arr[1]); break;
                    case '<=': $condition = floatval($arr[0]) <= floatval($arr[1]); break;
                    case '>=': $condition = floatval($arr[0]) >= floatval($arr[1]); break;
                    case '==': $condition = floatval($arr[0]) == floatval($arr[1]); break;
                    case '<': $condition = floatval($arr[0]) < floatval($arr[1]); break;
                    case '>': $condition = floatval($arr[0]) > floatval($arr[1]); break;
                }
            }
        }

        return !!self::val($condition) ? $if_true : $if_false;
    }

    /**
     * @param $conditions
     * @return bool
     */
    public static function ifAnd($conditions)
    {
        $result = true;
        $conditions = is_array($conditions) ? $conditions : [$conditions];
        foreach ($conditions as $el) {
            $result = $result && !!self::val($el);
        }
        return $result;
    }

    /**
     * @param $conditions
     * @return bool
     */
    public static function ifOr($conditions)
    {
        $result = false;
        $conditions = is_array($conditions) ? $conditions : [$conditions];
        foreach ($conditions as $el) {
            $result = $result || !!self::val($el);
        }
        return $result;
    }

    /**
     * @param $condition
     * @param array $cases
     * @param array $results
     * @param string $default
     * @return mixed
     */
    public static function switchCondition($condition, $cases = [], $results = [], $default = '')
    {
        $key = array_keys($cases, $condition)[0] ?? null;
        $var = $results[$key] ?? '';
        if ($var == '' && strlen($default)) {
            $var = $default;
        }
        if ($var == '' && count($results) > count($cases)) {
            $var = array_pop($results);
        }
        return $var;
    }

    /**
     * @param $question
     * @param $filePath
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function aiCreate($question, $filePath = null)
    {
        $repo = new UserConnRepository();
        $key = $repo->getUserApi(self::$metaTable->openai_tb_key_id ?: 0, true);
        if (! $key) {
            $key = $repo->defaultAiAcc(self::$userId);
        }

        $api = $key?->AiInteface();
        $response = '';
        if ($api) {
            if ($filePath) {
                $content = \Storage::get('public/'.$filePath);
                $response = $api->documentRecognition($question, $content, FileHelper::name($filePath));
            } else {
                $response = $api->textGeneration($question);
            }
        }

        return $response ?: 'No API Key available.';
    }

    /**
     * @param $promptString
     * @param $savingField
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function aiExtract($promptString, $savingField)
    {
        if (! $promptString) {
            return "Prompt is empty!";
        }

        $fileField = self::$metaTable->_fields->where('name', '=', $savingField)->first();
        $file = $fileField
            ? (new FileRepository())->getSql(self::$metaTable->id, $fileField->id, [self::$currentRow['id']])->first()
            : null;

        return self::aiCreate($promptString, $file?->filepath . $file?->filename);
    }

    /**
     * Add/Substract days or interval to Date.
     *
     * @param $date
     * @param $type
     * @param $duration
     * @return string
     */
    public static function timeChange($date, $type, $duration)
    {
        $date = self::checkDate($date);
        if (!$date) {
            return '';
        }

        $isSubstract = strtolower($type ?: 'substract') == 'substract' || $type == '-';

        $durationIsDate = DurationConvert::isDuration($duration) ? null : self::checkDate($duration);
        if ($durationIsDate) {
            return $isSubstract ? $durationIsDate->diffForHumans($date) : $date->diffForHumans($durationIsDate);
        }

        $duration = DurationConvert::isDuration($duration)
            ? DurationConvert::duration2second($duration)
            : ((int)$duration ?: 0);

        $f_type = self::$currentField['f_type'] ?? 'String';
        $format = self::$currentField['f_format'] ?? ($f_type == 'Date Time' ? 'Y-m-d H:i:s' : 'Y-m-d');

        return $isSubstract
            ? $date->subSeconds($duration)->format($format)
            : $date->addSeconds($duration)->format($format);
    }

    /**
     * @param null $date
     * @return Carbon|string
     */
    protected static function checkDate($date = null)
    {
        try {
            if (!$date || $date < Carbon::minValue()) {
                return '';
            }

            return new Carbon($date);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $timefrom
     * @param $timeto
     * @return int
     */
    public static function getDuration($timefrom, $timeto)
    {
        $timefrom = self::checkDate($timefrom);
        $timeto = self::checkDate($timeto);
        return $timefrom && $timeto
            ? $timefrom->diffInSeconds( $timeto, false )
            : null;
    }

    /**
     * Get Today.
     *
     * @param null $tz
     * @param null $format
     * @return string
     */
    public static function getToday($tz = null, $format = null)
    {
        $date = new Carbon(null, $tz ?: null);
        return ($format ? $date->format($format) : $date->toDateString());
    }

    /**
     * Get Tomorrow.
     *
     * @param null $tz
     * @param null $format
     * @return string
     */
    public static function getTomorrow($tz = null, $format = null)
    {
        $date = (new Carbon(null, $tz ?: null))->addDay();
        return ($format ? $date->format($format) : $date->toDateString());
    }

    /**
     * Get Yesterday.
     *
     * @param null $tz
     * @param null $format
     * @return string
     */
    public static function getYesterday($tz = null, $format = null)
    {
        $date = (new Carbon(null, $tz ?: null))->subDay();
        return ($format ? $date->format($format) : $date->toDateString());
    }

    /**
     * Get Day of a Year.
     *
     * @param $date
     * @return false|string
     */
    public static function getDateDay($date)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format("z") : '');
    }

    /**
     * Get Week of selected Date.
     *
     * @param $date
     * @return false|string
     */
    public static function getDateWeek($date)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format("W") : '');
    }

    /**
     * Get Week Day of selected Date.
     *
     * @param $date
     * @param null $argument
     * @return false|string
     */
    public static function getWeekDay($date, $argument = null)
    {
        $date = self::checkDate($date);
        $format = strtolower($argument) == 'name' ? "l" : "N";
        return ($date ? (new Carbon($date))->format($format) : '');
    }

    /**
     * Get Month of selected Date.
     *
     * @param $date
     * @param null $argument
     * @return false|string
     */
    public static function getDateMonth($date, $argument = null)
    {
        $date = self::checkDate($date);
        $format = strtolower($argument) == 'name' ? "F" : "n";
        return ($date ? (new Carbon($date))->format($format) : '');
    }

    /**
     * Get Year of selected Date.
     *
     * @param $date
     * @return false|string
     */
    public static function getDateYear($date)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format("Y") : '');
    }

    /**
     * Get date string
     *
     * @param $date
     * @param $format
     * @return string
     */
    public static function getDate($date, $format = null)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format($format ?: "Y-m-d") : '');
    }

    /**
     * Get time string
     *
     * @param $date
     * @param $format
     * @return string
     */
    public static function getTime($date, $format = null)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format($format ?: "H:i:s") : '');
    }

    /**
     * Get Day of a Month
     *
     * @param $date
     * @return false|string
     */
    public static function getDayMonth($date)
    {
        $date = self::checkDate($date);
        return ($date ? (new Carbon($date))->format("j") : '');
    }
}