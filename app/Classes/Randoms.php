<?php

namespace Vanguard\Classes;


class Randoms
{
    protected static $rand;

    /**
     * @return string
     */
    public static function rand_one()
    {
        return self::$rand = self::$rand ?: str_random(1);
    }
}