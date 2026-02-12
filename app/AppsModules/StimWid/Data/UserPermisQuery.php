<?php

namespace Vanguard\AppsModules\StimWid\Data;

use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class UserPermisQuery extends TableDataQuery
{
    /**
     * UserPermisQuery constructor.
     * @param Table $table
     * @param bool $no_join
     * @param null $prepare_relations_user
     */
    public function __construct(Table $table, $no_join = false, $prepare_relations_user = null)
    {
        parent::__construct($table, $no_join, $prepare_relations_user);
        $this->applyRowRightsForUser(auth()->id());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQueryForUser($query)
    {
        $this->query = $query;
        $this->applyRowRightsForUser(auth()->id());
        return $this->getQuery();
    }
}