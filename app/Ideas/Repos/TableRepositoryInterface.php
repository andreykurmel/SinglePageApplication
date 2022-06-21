<?php

namespace Vanguard\Ideas\Repos;


use Illuminate\Support\Collection;
use Vanguard\Ideas\QueriesOnlyFromRepo\Table;

interface TableRepositoryInterface
{
    public function get(array $ids): Collection;

    public function create(array $data): Table;

    public function delete(int $id): bool;

    public function first(int $id): Table;
}