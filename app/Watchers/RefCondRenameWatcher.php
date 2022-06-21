<?php

namespace Vanguard\Watchers;

use Illuminate\Support\Facades\DB;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class RefCondRenameWatcher
{
    /**
     * @param TableRefCondition $ref_cond
     * @param string $new_name
     */
    public function watchRename(TableRefCondition $ref_cond, string $new_name)
    {
        $table = $ref_cond->_table()->with('_fields_are_formula')->first();
        if ($table && $table->_fields_are_formula) {
            $dataQuery = new TableDataQuery($table);

            $fields_for_update = [];
            foreach ($table->_fields_are_formula as $header) {
                $fld = $dataQuery->getSqlFld($header->field . '_formula');
                $fields_for_update[$fld] = DB::raw('REPLACE(' . $fld . ', "\"' . $ref_cond->name . '\"", "\"' . $new_name . '\"")');
            }

            if (count($fields_for_update)) {
                $dataQuery->getQuery()->update($fields_for_update);
            }
        }
    }
}