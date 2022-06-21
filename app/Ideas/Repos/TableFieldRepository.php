<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\TableField;

class TableFieldRepository
{
    /**
     * @param int $table_id
     * @return Collection
     */
    public function getByTable(int $table_id): Collection
    {
        return TableField::db()
            ->where('table_id', '=', $table_id)
            ->get();
    }

}