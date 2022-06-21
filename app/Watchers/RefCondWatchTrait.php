<?php

namespace Vanguard\Watchers;

use Illuminate\Database\Eloquent\Collection;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;

trait RefCondWatchTrait
{
    /**
     * @var int[]
     */
    protected $calculated_references = [0];

    /**
     * @param int $table_id
     * @param int $lvl
     */
    public function watch(int $table_id, int $lvl = 0)
    {
        $references = $this->getGroupedReferences($table_id);
        $references = $this->filterReferences($references);
        $this->recalsSelectedReferences($references, $lvl);
    }

    /**
     * @param int $table_id
     */
    protected function getGroupedReferences(int $table_id)
    {
        $references = TableRefCondition::where('ref_table_id', $table_id)
            ->where('table_id', '!=', $table_id)
            ->whereNotIn('id', $this->calculated_references)
            ->with(['_table' => function ($q) {
                $q->select(['id','db_name','name','source','connection_id']);
                $q->with('_fields:id,table_id,field,input_type');
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
     * @param int $lvl
     */
    protected function recalsSelectedReferences(Collection $references, int $lvl = 0)
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
                if ($lvl < 3) {
                    $this->watch($ref_table->id, $lvl + 1);
                }
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