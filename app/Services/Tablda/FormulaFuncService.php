<?php


namespace Vanguard\Services\Tablda;


use Carbon\Carbon;
use Carbon\CarbonInterval;
use Vanguard\Classes\DurationConvert;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class FormulaFuncService
{
    /**
     * @var array
     */
    public static $currentField = [];
    /**
     * @var array
     */
    public static $currentRow = [];
    /**
     * @var null|Table
     */
    public static $metaTable = null;

    /**
     * @var array
     */
    private static $cacher = [];

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
    public static function sqlAgregate($type, $ref_cond_str, $field)
    {
        /*$ref_cond_id = [];
        preg_match('/\\[(.*)\\]/i', $ref_cond_str, $ref_cond_id);
        $ref_cond_id = $ref_cond_id[1] ?? null;*/

        //empty required fields
        if (!$ref_cond_str || !$field) {
            return null;
        }

        $table = null;
        $refCondition = null;

        if ($ref_cond_str == '$this') {
            $table = self::$metaTable;
        } else {
            //get Ref Condtion
            if (self::$metaTable && self::$metaTable->_ref_conditions) {
                foreach (self::$metaTable->_ref_conditions as $ref_cond) {
                    if ($ref_cond->name == $ref_cond_str) {
                        $refCondition = $ref_cond;
                    }
                }
            }
            $table = $refCondition ? $refCondition->_ref_table : null;
        }

        if (!$table) {
            return null;
        }

        //get db_name from Field
        $db_field = $table->_fields->filter(function($el) use ($field) {
            return preg_replace('/[^\w\d]/', '', $el->name) == preg_replace('/[^\w\d]/', '', $field);
        })->first();
        if (!$db_field) {
            return null;
        }
        //-------

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
            ? ($val1 ?: true)
            : ($val2 ?: false);
    }

    /**
     * @param $condition
     * @param $if_true
     * @param null $if_false
     * @return null
     */
    public static function ifCondition($condition, $if_true, $if_false = null)
    {
        //convert Duration
        $string = [];
        preg_match('/\'(.+)\'|"(.+)"/i', $condition, $string);
        if (count($string) == 2 || count($string) == 3) {
            $string[1] = $string[1] ?: $string[2];
            if (count($string) >= 2 && DurationConvert::isDuration($string[1])) {
                $string[1] = DurationConvert::duration2second($string[1]);
                $condition = str_replace($string[0], $string[1], $condition);
            }
        }

        //Calc condition
        $avail_operators = ['==', '!=', '<=', '>=', '<>', '<', '>'];
        foreach ($avail_operators as $operator) {
            if (strpos($condition, $operator) !== false) {
                $arr = explode($operator, $condition);
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
        foreach ($conditions as $el) {
            $result = $result || !!self::val($el);
        }
        return $result;
    }

    /**
     * @param $condition
     * @param array $cases
     * @param array $results
     * @return mixed
     */
    public static function switchCondition($condition, $cases = [], $results = [])
    {
        $key = array_keys($cases, $condition)[0] ?? null;
        $var = $results[$key] ?? '';
        if ($var == '' && count($results) > count($cases)) {
            $var = array_pop($results);
        }
        return $var;
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

        $f_type = self::$currentField['f_type'] ?? 'String';
        $format = self::$currentField['f_format'] ?? ($f_type == 'Date Time' ? 'Y-m-d H:i:s' : 'Y-m-d');
        if (strtolower($type ?: 'substract') == 'substract') {
            return $date->subSeconds((int)$duration ?: 0)->format($format);
        } else {
            return $date->addSeconds((int)$duration ?: 0)->format($format);
        }
    }

    /**
     * @param null $date
     * @return Carbon|string
     */
    private static function checkDate($date = null)
    {
        try {
            if (!$date || $date < Carbon::minValue()) {
                $date = '0001-01-01 00:00:00';
            }
            if (!preg_match('/\s\d\d:\d\d:\d\d/i', $date)) {
                $date .= ' 00:00:00';
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
        return $timeto ? $timeto->diffInSeconds( $timefrom, false ) : null;
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