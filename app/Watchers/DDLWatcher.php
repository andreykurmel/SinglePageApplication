<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;

class DDLWatcher
{
    /**
     * @param Table $table
     * @param array $new_row
     * @param array $old_row
     */
    public function watch(Table $table, array $new_row, array $old_row)
    {
        $dataRepo = new TableDataRepository();
        $ddlRepo = new DDLRepository();

        foreach ($table->_fields as $fld) {
            $key = $fld->field;
            //if value is changed
            if ( ($new_row[$key]??'') != ($old_row[$key]??'') )
            {
                //find DDLs of other tables where has 'auto-pop' and is not 'id/show'
                $ddls = $ddlRepo->findWatchingDDLs($fld->table_id, $fld->id);

                //Do update
                foreach ($ddls as $ddl) {
                    foreach ($ddl->_applied_to_fields as $app_fld) {
                        foreach ($ddl->_references as $ref) {
                            $dataRepo->updateFromChangedDDL($app_fld, $ref->_ref_condition, $old_row[$key], $new_row[$key]);
                        }
                    }
                }

            }
        }
    }
}