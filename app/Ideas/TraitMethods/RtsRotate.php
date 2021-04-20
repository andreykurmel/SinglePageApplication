<?php

namespace Vanguard\Ideas\TraitMethods;


class RtsRotate implements RtsInterface
{
    use RtsAble {
        RtsAble::set_fields as trait_set_fields;
    }

    protected $axis;
    protected $about_id;
    protected $deg;

    protected $px = 0;
    protected $py = 0;
    protected $pz = 0;

    protected $angle_x = 0;
    protected $angle_y = 0;
    protected $angle_z = 0;

    /**
     * RtsTranslate constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->axis = $params['axis'] ?? 'y';
        $this->about_id = floatval($params['about_id'] ?? 0);
        $this->deg = floatval($params['deg'] ?? 0) * -1/*reverse rotation*/;
    }

    /**
     * @param string $app_table
     * @return string
     */
    public function set_fields(string $app_table): string
    {
        $err = $this->trait_set_fields($app_table);

        if ($this->about_id) {
            $table = \DB::table( $this->app_tb );
            $node = $table->where('id', '=', $this->about_id)->first();

            if ($node) {
                $this->px = floatval( $node->{$this->fld_dx} );
                $this->py = floatval( $node->{$this->fld_dy} );
                $this->pz = floatval( $node->{$this->fld_dz} );
            }
        }

        switch ($this->axis) {
            case 'x': $this->angle_x = floatval( $this->deg ) * 3.1415 / 180; break;
            case 'y': $this->angle_y = floatval( $this->deg ) * 3.1415 / 180; break;
            case 'z': $this->angle_z = floatval( $this->deg ) * 3.1415 / 180; break;
        }

        return $err;
    }


    /**
     * @param array $row
     * @return array
     */
    public function calc_row(array $row): array
    {
        $cosa = cos($this->angle_z);
        $sina = sin($this->angle_z);

        $cosb = cos($this->angle_y);
        $sinb = sin($this->angle_y);

        $cosc = cos($this->angle_x);
        $sinc = sin($this->angle_x);

        $Axx = $cosa*$cosb;
        $Axy = $cosa*$sinb*$sinc - $sina*$cosc;
        $Axz = $cosa*$sinb*$cosc + $sina*$sinc;

        $Ayx = $sina*$cosb;
        $Ayy = $sina*$sinb*$sinc + $cosa*$cosc;
        $Ayz = $sina*$sinb*$cosc - $cosa*$sinc;

        $Azx = -$sinb;
        $Azy = $cosb*$sinc;
        $Azz = $cosb*$cosc;

        $loc_x = floatval($row[$this->fld_dx]) - $this->px;
        $loc_y = floatval($row[$this->fld_dy]) - $this->py;
        $loc_z = floatval($row[$this->fld_dz]) - $this->pz;

        $row[$this->fld_dx] = $Axx*$loc_x + $Axy*$loc_y + $Axz*$loc_z + $this->px;
        $row[$this->fld_dy] = $Ayx*$loc_x + $Ayy*$loc_y + $Ayz*$loc_z + $this->py;
        $row[$this->fld_dz] = $Azx*$loc_x + $Azy*$loc_y + $Azz*$loc_z + $this->pz;

        return $row;
    }
}