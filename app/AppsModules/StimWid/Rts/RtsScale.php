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
        $this->dx = floatval($params['dx'] ?? 1);
        $this->dy = floatval($params['dy'] ?? 1);
        $this->dz = floatval($params['dz'] ?? 1);
    }


    /**
     * @param array $row
     * @return array
     */
    public function calc_row(array $row): array
    {
        $row[$this->fld_dx] = floatval($row[$this->fld_dx]) * $this->dx;
        $row[$this->fld_dy] = floatval($row[$this->fld_dy]) * $this->dy;
        $row[$this->fld_dz] = floatval($row[$this->fld_dz]) * $this->dz;
        return $row;
    }
}