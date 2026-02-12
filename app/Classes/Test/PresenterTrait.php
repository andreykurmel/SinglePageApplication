<?php

namespace Vanguard\Classes\Test;


trait PresenterTrait
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @param array $fields
     */
    protected function setModel(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->fields->{$name};
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->fields->{$name} = $value;
    }

}