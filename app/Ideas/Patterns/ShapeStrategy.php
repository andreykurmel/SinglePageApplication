<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 06.05.21
 * Time: 15:19
 */

namespace Vanguard\Ideas\Patterns;


class ShapeStrategy
{
    static public function getFactory(string $type)
    {
        switch ($type) {
            case 'wood': return new WoodFactory();
            case 'steel': return new SteelFactory();
            default: throw new \Exception('Undefined factory');
        }
    }
}