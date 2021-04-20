<?php

namespace Vanguard\Repositories\Tablda\TableData;

class MselectData
{

    /**
     * Special converts if input_type = 'M-Select'
     *
     * @param array $all_vals
     * @param string $input_type
     * @return array
     */
    public static function convert(Array $all_vals, string $input_type)
    {
        if (self::isMSEL($input_type)) {
            $uniqued = [];
            foreach ($all_vals as $val) {
                $arr = json_decode($val, 1);
                if (is_array($arr)) {
                    $uniqued = array_merge($uniqued, $arr);
                } else {
                    $uniqued = array_merge($uniqued, [$val]);
                }
            }
            $all_vals = [];
            foreach (array_unique($uniqued) as $val) {
                $all_vals[] = $val;
            };
        }
        return $all_vals;
    }

    /**
     * @param string $input_type
     * @return bool
     */
    public static function isMSEL(string $input_type)
    {
        return in_array($input_type, ['M-Select','M-Search','M-SS']);
    }

    /**
     * @param string $input
     * @return mixed|string
     */
    public static function tryParse(string $input)
    {
        if ($input && in_array($input[0], ['[', '{'])) {
            return json_decode($input, true);
        } else {
            return $input;
        }
    }
}