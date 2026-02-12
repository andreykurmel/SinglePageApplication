<?php

namespace Vanguard\AppsModules\StimWid\Data;


use Illuminate\Database\Eloquent\Builder;
use Tablda\DataReceiver\DataTableReceiver;
use Tablda\DataReceiver\TabldaTable;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class TableConverter extends DataTableReceiver
{
    public function __construct(TabldaTable $model, $case_sens = null)
    {
        $this->case_sens = $case_sens;
        $this->model = $model;
        $this->clearQuery();
    }

    /**
     * Clear Builder Query;
     */
    public function clearQuery() {
        $table = (new TableRepository())->getTableByDB($this->model->getTable());
        $this->builder = TableDataQuery::getTableDataSql($table);
    }

    /**
     * @return array|null
     */
    public function first()
    {
        $model = $this->builder->first();
        if ($model) {
            return $this->map_data($model->toArray(), true);
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        $models = $this->builder->get()->toArray();
        foreach ($models as $i => $m) {
            $models[$i] = $this->map_data($m, true);
        }
        return $models;
    }

    /**
     * @param array $input
     * @param bool $reverse
     * @return array
     */
    protected function map_data(array $input, bool $reverse = false) {
        $maps = $this->model->getMaps();
        if ($reverse) {
            $maps = array_flip($maps);
        }
        $mapped_data = [];
        foreach ($input as $key => $val) {
            $mapper = $maps[ $this->t_case($key) ] ?? null;
            if ($mapper) {
                $mapped_data[ $this->t_case($mapper) ] = $val;
            }
        }
        return $mapped_data;
    }

    /**
     * not_systems
     */
    public function not_systems()
    {
//        $this->builder->whereRaw('(`row_hash` != "cf_temp" OR `row_hash` IS NULL)');
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