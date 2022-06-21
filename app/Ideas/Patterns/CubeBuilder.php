<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 16:11
 */

namespace Vanguard\Ideas\Patterns;


class CubeBuilder
{
    /**
     * @param float $wi
     * @param float $he
     * @param float $de
     * @return CubeShape
     */
    public function buildWoodCube(float $wi, float $he, float $de): CubeShape
    {
        $m = new WoodMaterial();
        return $this->buildCube($m, $wi, $he, $de);
    }

    /**
     * @param float $wi
     * @param float $he
     * @param float $de
     * @return CubeShape
     */
    public function buildSteelCube(float $wi, float $he, float $de): CubeShape
    {
        $m = new SteelMaterial();
        return $this->buildCube($m, $wi, $he, $de);
    }

    /**
     * @param Material $m
     * @param float $wi
     * @param float $he
     * @param float $de
     * @return CubeShape
     */
    protected function buildCube(Material $m, float $wi, float $he, float $de): CubeShape
    {
        $cube = new CubeShape($m);
        $cube->setSizes($wi, $he, $de);
        return $cube;
    }
}