<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\DDL;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
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
        $table->loadMissing('_fields');
        $daraRepo = new TableDataRepository();

        foreach ($new_row as $key => $value) {

            $fld = $table->_fields->where('field', $key)->first();

            //if value is changed
            if ($fld && isset($old_row[$key]) && $new_row[$key] != $old_row[$key])
            {
                //find DDLs where is used Header
                $ddls = DDL::whereHas('_references', function ($ref) use ($fld) {
                    $ref->where('target_field_id', $fld->id);
                })->get();

                //Find Fields for update
                $ref_fields = TableField::where('input_type', '!=', 'Input')
                    ->whereIn('table_id', $ddls->pluck('table_id'))
                    ->whereIn('ddl_id', $ddls->pluck('id'))
                    ->with('_table')
                    ->get();

                foreach ($ref_fields as $r_f) {
                    //Do update
                    $daraRepo->updateField($r_f, $old_row[$key], $new_row[$key]);

                    //recursive call
                    $oldr = [$r_f->field => $old_row[$key]];
                    $newr = [$r_f->field => $new_row[$key]];
                    $this->watch($r_f->_table, $newr, $oldr);
                }
            }
        }
    }
}