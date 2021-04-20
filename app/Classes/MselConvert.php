<?php

namespace Vanguard\Classes;


class MselConvert
{
    /**
     * parse M-Select val
     *
     * @param $val
     * @return array|mixed
     */
    public static function getArr($val)
    {
        try {
            $res = json_decode($val);
        } catch (\Exception $e) {
            $res = [$val];
        }

        return is_array($res) ? $res : [$val];
    }
}