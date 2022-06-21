<?php

namespace Vanguard\Classes;

class DurationConvert
{
    /**
     * @param string $test
     * @return bool
     */
    public static function isDuration(string $test): bool
    {
        return preg_match('/\d\s?[wkdhms]/i', $test);
    }

    /**
     * @param string $duration
     * @return string
     */
    protected static function prepareDuration(string $duration): string
    {
        $duration = preg_replace('/\.\s/i', ' ', $duration);
        $duration = preg_replace('/\(\)/i', '', $duration);

        $duration = preg_replace('/weeks?/i', 'w', $duration);
        $duration = preg_replace('/days?/i', 'd', $duration);
        $duration = preg_replace('/hours?/i', 'h', $duration);
        $duration = preg_replace('/minutes?/i', 'm', $duration);
        $duration = preg_replace('/seconds?/i', 's', $duration);

        $duration = preg_replace('/wks?/i', 'w', $duration);
        $duration = preg_replace('/hrs?/i', 'h', $duration);
        $duration = preg_replace('/mins?/i', 'm', $duration);
        $duration = preg_replace('/secs?/i', 's', $duration);

        return $duration;
    }

    /**
     * @param string $duration
     * @return int
     */
    public static function duration2second(string $duration): int
    {
        $duration = self::prepareDuration($duration);
        $variants = ['w'=>604800, 'd'=>86400, 'h'=>3600, 'm'=>60, 's'=>1];
        $result = 0;
        $parts = preg_replace('/<[^>]*>/i', '', $duration); //remove tags

        //change fractions: 3/5 => 0.6
        $parts = preg_replace_callback('/([\.\d]+)\/([\.\d]+)/i', function($match) {
            return floatval($match[1] / $match[2]);
        }, $parts);

        $minus = strpos($parts, '-');

        $parts = preg_replace('/[^\.\dwkdhms]/i', '', $parts); //remove not available symbols
        $parts = preg_replace('/(wk|d|h|m|s|w)/i', '$1,', $parts); //prepare for splitting
        $parts = explode(',', $parts);

        foreach($parts as $el) {
            if ($el) {
                $val = floatval(trim($el));
                $key = preg_replace('/[^wkdhms]/i', '', trim($el));
                $key = strtolower($key);
                $result += floatval($val) * floatval($variants[$key] ?? 1);
            }
        };
        return $minus !== false
            ? -1*$result
            : $result;
    }
}