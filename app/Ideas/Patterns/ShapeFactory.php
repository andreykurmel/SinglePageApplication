<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 15:58
 */

namespace Vanguard\Ideas\Patterns;


interface ShapeFactory
{
    /**
     * @param float $radius
     * @return SphereShape
     */
    public function buildSphere(float $radius) : SphereShape;

    /**
     * @param float $wi
     * @param float $he
     * @param float $de
     * @return CubeShape
     */
    public function buildCube(float $wi, float $he, float $de) : CubeShape;
}