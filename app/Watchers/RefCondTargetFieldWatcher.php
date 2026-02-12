<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;

class RefCondTargetFieldWatcher
{
    use UpdateFormulaTrait;

    protected $processed_tables = [];

    /**
     * @param int $table_id
     * @param string $old_name
     * @param string $new_name
     */
    public function watchRename(int $table_id, string $old_name, string $new_name)
    {
        $references = TableRefCondition::where('ref_table_id', $table_id)
            ->with(['_table' => function ($q) {
                $q->with('_fields_are_formula');
            }])
            ->get();

        foreach ($references as $ref) {
            if (!in_array($ref->table_id, $this->processed_tables)) {
                $this->processed_tables[] = $ref->table_id;

                $table = $ref->_table;
                $this->updTableFormulas($table, '\"' . $old_name . '\"', '\"' . $new_name . '\"');

            }
        }

    }
}