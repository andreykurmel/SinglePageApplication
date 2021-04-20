<?php

namespace Vanguard\AppsModules\StimWid\Rts;


interface RtsInterface
{
    /**
     * @param string $app_table
     * @return string //Error
     */
    public function set_fields(string $app_table) : string;

    /**
     * @param array $row
     * @return array //Result row
     */
    public function calc_row(array $row) : array;

    /**
     * @return array //Settings row
     */
    public function get_app_tb() : array;
}