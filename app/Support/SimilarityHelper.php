<?php

namespace Vanguard\Support;

class SimilarityHelper
{
    /**
     * @param string $a
     * @param string $b
     * @return float
     */
    public static function check(string $a, string $b): float
    {
        if (!$a || !$b) {
            return 0;
        }

        $a_arr = self::tokenize($a);
        $b_arr = self::tokenize($b);
        
        $minCount =min(count($a_arr), count($b_arr));
        $intersection = count(array_intersect($a_arr, $b_arr));
        $overlap = $intersection / $minCount;

        $maxLen = max(strlen($a), strlen($b));
        $minLen = min(strlen($a), strlen($b));
        $charDistance = 1 - (($maxLen - $minLen) / $maxLen);

        $p = 0;
        similar_text(join(' ', $a_arr), join(' ', $b_arr), $p);
        $similarityPart = (1 - $overlap) * $p/100;

        return ($charDistance * 0.1)
            + (($overlap + $similarityPart) * 0.9);
    }

    /**
     * @param string $s
     * @return array
     */
    protected static function tokenize(string $s): array
    {
        // lower, trim, collapse whitespace, strip punctuation except digits/letters/spaces
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
        $s = preg_replace('/\s+/u', ' ', trim($s));
        if ($s === '') {
            return [];
        }
        return array_values(array_unique(explode(' ', $s)));
    }
}