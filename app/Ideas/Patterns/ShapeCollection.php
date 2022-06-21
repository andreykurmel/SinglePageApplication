<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 06.05.21
 * Time: 17:17
 */

namespace Vanguard\Ideas\Patterns;


class ShapeCollection
{
    protected $shapes = [];

    public function getShape(int $idx)
    {
        return $this->shapes[$idx] ?? '';
    }

    public function addShape(Shape $shape)
    {
        $this->shapes[] = $shape;
    }

    public function getLen()
    {
        return count($this->shapes);
    }

    public function shapeIterator()
    {
        return new ShapeIterator( $this );
    }
}