<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

class DDLWatcher
{
    /**
     * Update simple DDL value when source table->value has been changed
     * @param Table $table
     * @param array $new_row
     * @param array $old_row
     */
    public function watch(Table $table, array $new_row, array $old_row)
    {
        $dataRepo = new TableDataRepository();
        $ddlRepo = new DDLRepository();
        $refRepo = new TableRefConditionRepository();

        foreach ($table->_fields as $fld) {
            $key = $fld->field;
            $old = $old_row[$key] ?? '';
            $new = $new_row[$key] ?? '';
            //if value is changed
            if ( $new != $old )
            {
                //find DDLs of other tables where has 'auto-pop' and is not 'id/show'
                $ddls = $ddlRepo->findWatchingDDLs($fld->id);

                //Do update
                foreach ($ddls as $ddl) {
                    foreach ($ddl->_applied_to_fields as $app_fld) {
                        foreach ($ddl->_references as $ref) {
                            $dataRepo->updateFromChangedDDL($app_fld, $ref->_ref_condition, $old, $new);
                        }
                    }
                }

                $table_ids = $ddls->pluck('table_id')->toArray();
                foreach ($refRepo->findRefsToSync($table_ids, $old) as $ref) {
                    $ref->update([
                        'compared_value' => $new,
                        'spec_show' => $new,
                    ]);
                }

            }
        }
    }
}