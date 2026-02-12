<?php

namespace Vanguard\Services\Tablda;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class DuplicateDataService
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $uniques;
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $concats;
    /**
     * @var array
     */
    protected $to_delete = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->uniques = collect($parameters)
            ->filter(function($el) { return $el == 'unique'; })
            ->keys();
        $this->concats = collect($parameters)
            ->filter(function($el) { return $el == 'all'; })
            ->keys();
        $this->to_delete = [];
    }

    /**
     * @param Table $table
     * @return int
     */
    public function removeDuplicates(Table $table): int
    {
        $duplicates = collect([]);
        $sql = new TableDataQuery($table);
        $sql = $sql->getQuery();
        $row_sql = clone $sql;

        $page = 200;
        $lines = $sql->count();
        for ($cur = 0; ($cur * $page) < $lines; $cur++) {
            $rows = $sql->offset($cur * $page)
                ->limit($page)
                ->get();

            $this->proceedRows($rows, $duplicates, $row_sql);
        }
        $this->updateConcats($duplicates, $row_sql);
        $this->doRemove($row_sql);

        return $duplicates->count();
    }

    /**
     * @param Builder $row_sql
     * @return void
     */
    protected function doRemove(Builder $row_sql)
    {
        if ($this->to_delete) {
            (clone $row_sql)
                ->whereIn('id', $this->to_delete)
                ->delete();
        }
    }

    /**
     * @param Collection $duplicates
     * @param Builder $row_sql
     */
    protected function updateConcats(Collection $duplicates, Builder $row_sql)
    {
        foreach ($duplicates as $dbl) {
            (clone $row_sql)
                ->where('id', '=', $dbl['id'])
                ->update($dbl);
        }
    }

    /**
     * @param Collection $rows
     * @param Collection $duplicates
     * @param Builder $row_sql
     */
    protected function proceedRows(Collection $rows, Collection $duplicates, Builder $row_sql)
    {
        foreach ($rows as $r) {
            $key = $this->duplicateKey($r);
            if (empty($duplicates[$key])) {

                $dup = [ 'id' => $r['id'] ];
                foreach ($this->concats as $con) {
                    $dup[$con] = $r[$con]??'';
                }
                $duplicates[$key] = $dup;

            } else {

                $dup = $duplicates[$key];
                foreach ($this->concats as $con) {
                    if (!empty($r[$con])) {
                        $dup[$con] .= ', ' . $r[$con];
                    }
                }
                $duplicates[$key] = $dup;

                $this->to_delete[] = $r['id'];
            }
        }
    }

    /**
     * @param $row
     * @return string
     */
    protected function duplicateKey($row): string
    {
        $key = [];
        foreach ($this->uniques as $k) {
            $key[] = trim($row[$k] ?? '');
        }
        return implode('_', $key);
    }
}