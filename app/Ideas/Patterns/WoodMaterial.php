<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 05.05.21
 * Time: 14:55
 */

namespace Vanguard\Ideas\Patterns;


class WoodMaterial extends Material
{
    /**
     * Material constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->density = 960;
    }
}