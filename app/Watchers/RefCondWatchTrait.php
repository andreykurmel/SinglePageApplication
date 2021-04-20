<?php

namespace Vanguard\Watchers;

use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;

trait RefCondWatchTrait
{
    protected $calculated_references = [];

    /**
     * @param Table $table
     */
    public function watch(Table $table)
    {
        $references = $this->getGroupedReferences($table);
        $references = $this->filterReferences($references);
        $this->recalsSelectedReferences($references);
    }

    /**
     * @param Table $table
     */
    protected function getGroupedReferences(Table $table)
    {
        $references = TableRefCondition::where('ref_table_id', $table->id)
            ->where('table_id', '!=', $table->id)
            ->whereNotIn('id', $this->calculated_references)
            ->with(['_table' => function ($q) {
                $q->with('_fields');
            }])
            ->get();
        return $references->groupBy('table_id');
    }

    /**
     * @param Collection $references
     * @return Collection
     */
    protected function filterReferences(Collection $references)
    {
        //filter ref conditions (get only used in formulas)
        foreach ($references as $key => $ref_group) {
            if (!$ref_group->count()) {
                continue;
            }

            $ref_table = $ref_group->first()->_table;
            $filtered_ref_groups = collect([]);
            foreach ($ref_group as $ref_cond) {
                if ($this->filterFunction($ref_table, $ref_cond)) {
                    $filtered_ref_groups[] = $ref_cond;
                }
            }
            $references[$key] = $filtered_ref_groups;
        }
        return $references;
    }

    /**
     * @param Collection $references
     */
    protected function recalsSelectedReferences(Collection $references)
    {
        //recalc references
        foreach ($references as $ref_group) {
            if (!$ref_group->count()) {
                continue;
            }

            $ref_table = $ref_group->first()->_table;
            $ref_ids = $ref_group->pluck('id')->toArray();

            if ($ref_table && array_diff($ref_ids, $this->calculated_references)) {
                $this->calculated_references = array_merge($this->calculated_references, $ref_ids);

                $this->doAction($ref_table);

                //recursive checking
                $this->watch($ref_table);
            }
        }
    }

    /**
     * @param Table $ref_table
     * @param TableRefCondition $ref_cond
     * @return bool
     */
    protected abstract function filterFunction(Table $ref_table, TableRefCondition $ref_cond): bool;

    /**
     * @param Table $ref_table
     */
    protected abstract function doAction(Table $ref_table): void;
}