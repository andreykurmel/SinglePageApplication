<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;
use Vanguard\Ideas\QueriesOnlyFromRepo\TableElem;
use Vanguard\Ideas\QueriesOnlyFromRepo\TableEntity;

class CachedTableRepository extends TableRepository
{
    use ModelCacherTrait;

    /**
     * CachedTableRepository constructor.
     */
    public function __construct()
    {
        $this->cache_str = 'table-repository';
        parent::__construct();
    }


    /**
     * @param array $ids
     * @param $user_id
     * @return Collection
     */
    public function get(array $ids, $user_id = null): Collection
    {
        return $this->rememberArray($ids, 'id', function ($rest_ids) use ($user_id) {
            return parent::get($rest_ids, $user_id);
        });
    }


}