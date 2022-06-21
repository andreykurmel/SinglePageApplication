<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 15:25
 */

namespace Vanguard\Ideas\Patterns;


class WeightAdapter
{
    /**
     * @var Material
     */
    protected $material;

    /**
     * WeightAdapter constructor.
     * @param Material $material
     */
    public function __construct(Material $material)
    {
        $this->material = $material;
    }

    /**
     * kg/m3 => g/cm3
     * @return float
     */
    public function densityAsGramms()
    {
        return $this->material->getDensity() / 1000;
    }
}