<?php

namespace Vanguard\Classes;


use Illuminate\Support\Str;

class Randoms
{
    protected static $rand;

    /**
     * @return string
     */
    public static function rand_one()
    {
        return self::$rand = self::$rand ?: Str::random(1);
    }
}