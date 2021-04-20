<?php

namespace Vanguard\Ideas\TraitMethods;


trait RtsAble
{
    protected $app_tb;
    protected $fld_dx;
    protected $fld_dy;
    protected $fld_dz;

    /**
     * @param string $app_table
     * @return string
     */
    public function set_fields(string $app_table): string
    {
        $this->app_tb = $app_table;

        if (!$this->app_tb) {
            return 'Incorrect settings in Correspondence/Fields.tbl';
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function get_app_tb(): array
    {
        return $this->app_tb;
    }
}