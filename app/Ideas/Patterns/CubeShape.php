<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 14:55
 */

namespace Vanguard\Ideas\Patterns;


class CubeShape extends Shape
{
    /**
     * cm
     * @var float
     */
    protected $width;
    /**
     * cm
     * @var float
     */
    protected $height;
    /**
     * cm
     * @var float
     */
    protected $depth;

    /**
     * CubeShape constructor.
     * @param Material $mat
     */
    public function __construct(Material $mat)
    {
        parent::__construct($mat);
    }

    /**
     * @param float $width
     * @param float $height
     * @param float $depth
     */
    public function setSizes(float $width, float $height, float $depth)
    {
        $this->width = $width;
        $this->height = $height;
        $this->depth = $depth;
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
        return $this->width * $this->height * $this->depth;
    }

}