<?php

namespace Vanguard\AppsModules\StimWid\Rts;


use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\StimSettingsRepository;

trait RtsAble
{
    protected $app_tb;
    protected $fld_dx;
    protected $fld_dy;
    protected $fld_dz;

    /**
     * @param $input
     * @return float|int
     */
    public function parse_param($input)
    {
        if (preg_match('/\//i', $input)) {
            $part = explode('/', $input);
            return floatval($part[0]??1) / floatval($part[1]??1);
        } else {
            return floatval($input);
        }
    }

    /**
     * @param string $app_table
     * @return string
     */
    public function set_fields(string $app_table): string
    {
        $stimRepo = new StimSettingsRepository();
        $this->app_tb = DataReceiver::app_table_and_fields($app_table);
        $this->fld_dx = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_rts:dx', false, true);
        $this->fld_dy = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_rts:dy', false, true);
        $this->fld_dz = $stimRepo->getDataFields($this->app_tb['_app_fields'], 'is_rts:dz', false, true);

        if (!$this->fld_dx || !$this->fld_dy || !$this->fld_dz) {
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