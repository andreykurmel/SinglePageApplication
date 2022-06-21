<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 16:02
 */

namespace Vanguard\Ideas\Patterns;


class SteelFactory implements ShapeFactory
{
    /**+
     * @param float $radius
     * @return SphereShape
     */
    public function buildSphere(float $radius): SphereShape
    {
        return new SphereShape(new SteelMaterial(), $radius);
    }

    /**
     * @param float $wi
     * @param float $he
     * @param float $de
     * @return CubeShape
     */
    public function buildCube(float $wi, float $he, float $de): CubeShape
    {
        return (new CubeBuilder())->buildSteelCube($wi, $he, $de);
    }

}