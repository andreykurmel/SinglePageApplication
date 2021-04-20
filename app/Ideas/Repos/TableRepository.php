<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;
use Vanguard\Ideas\QueriesOnlyFromRepo\TableElem;
use Vanguard\Ideas\QueriesOnlyFromRepo\TableEntity;

class TableRepository
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $tables;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->tables = collect([]);
    }



    //TEST DIFFERENT APPROACHES
    /**
     * @param int $id
     * @return TableElem
     */
    public function getArray(int $id): TableElem
    {
        $table = Table::where('id', '=', $id)->first();

        return $table
            ? new TableElem($table->toArray())
            : null;
    }

    /**
     * @param int $id
     * @return TableEntity
     */
    public function getModel(int $id): TableEntity
    {
        $table = Table::where('id', '=', $id)->first();

        return $table
            ? (new TableEntity())->forceFill($table->toArray())
            : null;
    }
    //TEST DIFFERENT APPROACHES


    /**
     * @param array $ids
     * @param $user_id
     * @return Collection
     */
    public function get(array $ids, $user_id = null): Collection
    {
        return Table::whereIn('id', $ids)
            ->get()
            ->map(function ($el) {
                return new TableElem($el->toArray());
            });
    }
}