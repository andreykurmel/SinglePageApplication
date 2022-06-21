<?php

namespace Vanguard\Helpers;

class DateHelper
{
    /**
     * @param $value
     * @return bool
     */
    public static function isDate($value): bool
    {
        if (!$value || !is_string($value)) {
            return false;
        }

        $parsed = date_parse($value);
        return $parsed
            && !empty($parsed['year'])
            && !empty($parsed['month'])
            && !empty($parsed['day'])
            && empty($parsed['error_count'])
            && empty($parsed['warning_count']);
    }
}