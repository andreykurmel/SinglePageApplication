<?php

namespace Vanguard\Classes;

class Tablda
{
    /**
     * @param string $name
     * @return string
     */
    public static function safeName(string $name): string
    {
        return preg_replace('/[^\w\d\(\)\-\.,_ ]/i', '', $name);
    }
}