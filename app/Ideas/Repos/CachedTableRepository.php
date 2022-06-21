<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;

class CachedTableRepository extends TableRepository
{
    use ModelCacherTrait;

    /**
     * CachedTableRepository constructor.
     */
    public function __construct()
    {
        $this->cache_str = 'tables';
        parent::__construct();
    }


    /**
     * @param array $ids
     * @param int|null $user_id
     * @return Collection
     */
    public function get(array $ids, int $user_id = null): Collection
    {
        return $this->rememberArray($ids, 'id', function ($rest_ids) use ($user_id) {
            return parent::get($rest_ids, $user_id);
        });
    }

    /**
     * @param int $id
     * @return Table
     */
    public function first(int $id): Table
    {
        return $this->rememberSingle($id, 'id', function () use ($id) {
            return parent::first($id);
        });
    }


}