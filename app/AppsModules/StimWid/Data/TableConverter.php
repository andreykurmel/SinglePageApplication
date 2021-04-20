<?php

namespace Vanguard\AppsModules\StimWid\Data;


use Illuminate\Database\Eloquent\Builder;
use Tablda\DataReceiver\DataTableReceiver;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class TableConverter extends DataTableReceiver
{
    /**
     * not_systems
     */
    public function not_systems()
    {
        $this->builder->where(function ($inner) {
            $inner->whereNotIn('row_hash', (new HelperService())->sys_row_hash);
            $inner->orWhereNull('row_hash'); //Because only 'Where Not In' cannot get records with NULL
        });
    }

    /**
     * apply_user_where
     */
    public function apply_user_where()
    {
        $this->builder->where(function ($inner) {
            $tb = (new TableRepository())->getTableByDB( $this->model->getTable() );
            if ($tb) {
                $inner = (new UserPermisQuery($tb))->getQueryForUser($inner);
            }
        });
    }

    /**
     * @param array $filters
     */
    public function apply_filters(array $filters)
    {
        $tb = (new TableRepository())->getTableByDB( $this->model->getTable() );
        $dataQuery = new TableDataQuery($tb);
        $dataQuery->setQuery($this->builder);
        $dataQuery->externalFilters($filters);
    }

    /**
     * @return string
     */
    public function getModelTable() {
        return $this->model->getTable();
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->builder;
    }
}