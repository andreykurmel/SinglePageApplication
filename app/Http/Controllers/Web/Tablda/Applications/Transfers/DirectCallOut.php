<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers;


class DirectCallOut
{
    /**
     * @var array
     */
    protected $row;

    /**
     * @param array $row
     */
    public function setRow(array $row)
    {
        $this->row = $row;
    }

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }
}