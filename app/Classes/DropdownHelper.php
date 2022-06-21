<?php

namespace Vanguard\Classes;


class DropdownHelper
{
    /**
     * @param string $mirfield
     * @param $ref_row
     * @return mixed|string
     */
    public static function valOrShow(string $mirfield, $ref_row)
    {
        $val = $ref_row[$mirfield] ?? '';
        if (!empty($ref_row['_rc_' . $mirfield])) {
            $rc_obj = (array)($ref_row['_rc_' . $mirfield][$val] ?? []);
            $val = $rc_obj['show_val'] ?? $val;
        }
        return $val;
    }
}