<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;

trait AttributeObject
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * AttributeObject constructor.
     * @param array $attributes
     */
    function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param $key
     * @return null
     */
    public function getAttribute($key)
    {
        //Attributes
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        //Relations
        if (method_exists($this, $key)) {
            $this->setAttribute($key, $this->$key());
            return $this->attributes[$key];
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * @param $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}