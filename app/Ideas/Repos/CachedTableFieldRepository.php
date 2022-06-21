<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;

class CachedTableFieldRepository extends TableFieldRepository
{
    use ModelCacherTrait;

    /**
     * CachedTableRepository constructor.
     */
    public function __construct()
    {
        $this->cache_str = 'table_fields';
    }


    /**
     * @param int $table_id
     * @return Collection
     */
    public function getByTable(int $table_id): Collection
    {
        return $this->rememberObject('table_id.'.$table_id, function () use ($table_id) {
            return parent::getByTable($table_id);
        });
    }

}