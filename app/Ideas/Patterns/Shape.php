<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 14:55
 */

namespace Vanguard\Ideas\Patterns;


abstract class Shape
{
    /**
     * @var Material
     */
    protected $material;

    /**
     * Shape constructor.
     * @param Material $mat
     */
    public function __construct(Material $mat)
    {
        $this->material = $mat;
    }

    /**
     * @return float
     */
    abstract public function getVolume() : float;

    /**
     * @return float
     */
    abstract public function getWeight() : float;
}