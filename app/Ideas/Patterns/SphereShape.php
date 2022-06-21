<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 14:55
 */

namespace Vanguard\Ideas\Patterns;


class SphereShape extends Shape
{
    /**
     * cm
     * @var float
     */
    protected $radius;

    /**
     * SphereShape constructor.
     * @param Material $mat
     * @param float $radius
     */
    public function __construct(Material $mat, float $radius)
    {
        parent::__construct($mat);
        $this->radius = $radius;
    }

    /**
     * g/cm3
     * @return float
     */
    public function getWeight(): float
    {
        return $this->getVolume() * (new WeightAdapter($this->material))->densityAsGramms();
    }

    /**
     * cm3
     * @return float
     */
    public function getVolume(): float
    {
        return 4 / 3 * M_PI * pow($this->radius, 3);
    }

}