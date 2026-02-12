<?php

namespace Vanguard\Classes\Test;


use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;

class TablePresenter
{
    use PresenterTrait;

    /**
     * TablePresenter constructor.
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $table->loadMissing('_fields');
        $this->setModel($table->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->fields;
    }
}