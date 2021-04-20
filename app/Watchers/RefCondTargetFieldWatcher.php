<?php

namespace Vanguard\Watchers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class RefCondTargetFieldWatcher
{
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
                if ($table && $table->_fields_are_formula) {
                    $dataQuery = new TableDataQuery($table);

                    $fields_for_update = [];
                    foreach ($table->_fields_are_formula as $header) {
                        $fld = $dataQuery->getSqlFld($header->field . '_formula');
                        $fields_for_update[$fld] = DB::raw('REPLACE(' . $fld . ', "\"' . $old_name . '\"", "\"' . $new_name . '\"")');
                    }
                    if ($fields_for_update) {
                        $dataQuery->getQuery()->update($fields_for_update);
                    }
                }

            }
        }

    }
}