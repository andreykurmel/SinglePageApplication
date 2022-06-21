<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 06.05.21
 * Time: 17:18
 */

namespace Vanguard\Ideas\Patterns;


class ShapeIterator
{
    protected $collection;
    protected $pos;
    protected $len;

    public function __construct(ShapeCollection $collection)
    {
        $this->pos = -1;
        $this->len = $collection->getLen();
        $this->collection = $collection;
    }

    public function next()
    {
        $this->pos++;
        return $this->pos < $this->len ? $this->collection->getShape( $this->pos ) : null;
    }
}