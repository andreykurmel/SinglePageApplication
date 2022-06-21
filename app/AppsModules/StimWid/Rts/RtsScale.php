<?php

namespace Vanguard\AppsModules\StimWid\Rts;


class RtsScale implements RtsInterface
{
    use RtsAble;

    protected $dx;
    protected $dy;
    protected $dz;

    /**
     * RtsTranslate constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->dx = $this->parse_param($params['dx'] ?? 1);
        $this->dy = $this->parse_param($params['dy'] ?? 1);
        $this->dz = $this->parse_param($params['dz'] ?? 1);
    }


    /**
     * @param array $row
     * @return array
     */
    public function calc_row(array $row): array
    {
        $row[$this->fld_dx] = $this->parse_param($row[$this->fld_dx]) * $this->dx;
        $row[$this->fld_dy] = $this->parse_param($row[$this->fld_dy]) * $this->dy;
        $row[$this->fld_dz] = $this->parse_param($row[$this->fld_dz]) * $this->dz;
        return $row;
    }
}