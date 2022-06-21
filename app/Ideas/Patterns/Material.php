<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 14:55
 */

namespace Vanguard\Ideas\Patterns;


abstract class Material
{
    /**
     * kg/m3
     * @var float
     */
    protected $density;
    /**
     * hex color
     * @var string
     */
    protected $color;

    /**
     * Material constructor.
     */
    public function __construct()
    {
        $this->density = 0;
        $this->color = '';
    }

    /**
     * @param string $color
     */
    public function setColor(string $color)
    {
        $this->color = $color;
    }

    /**
     * hex color
     * @return string
     */
    public function getColor() : string
    {
        return $this->color;
    }

    /**
     * kg/m3
     * @return float
     */
    public function getDensity() : float
    {
        return $this->density;
    }
}