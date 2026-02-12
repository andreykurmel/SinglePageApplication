<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers;


use Vanguard\Models\Table\Table;

class DirectCallInput
{
    /**
     * @var Table
     */
    protected $table;
    protected $row = [];
    protected $old_row = [];

    /**
     * @param Table $table
     */
    public function setTable(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @param array $row
     */
    public function setRow(array $row)
    {
        $this->row = $row;
    }

    /**
     * @param array $row
     */
    public function setOldRow(array $row)
    {
        $this->old_row = $row;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return array
     */
    public function getOldRow()
    {
        return $this->old_row;
    }
}