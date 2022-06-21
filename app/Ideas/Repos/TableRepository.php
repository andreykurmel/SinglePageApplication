<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;

class TableRepository implements TableRepositoryInterface
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


    /**
     * Productivity: Slower at 15% than get()
     *
     * @param array $ids
     * @param int|null $user_id
     * @return Collection
     */
    public function get(array $ids, int $user_id = null): Collection
    {
        $sql = Table::db()->whereIn('id', $ids);
        if ($user_id) {
            $sql->where('user_id', '=', $user_id);
        }
        return $sql->get();
    }

    /**
     * @param array $data
     * @return Table
     */
    public function create(array $data): Table
    {
        $new = Table::db()->insert($data);
        return new Table( array_merge($data, ['id' => $new->id]) );
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Table::db()->where('id', '=', $id)->delete();
    }

    /**
     * @param int $id
     * @return Table
     */
    public function first(int $id): Table
    {
        return Table::db()->where('id', '=', $id)->first();
    }


}