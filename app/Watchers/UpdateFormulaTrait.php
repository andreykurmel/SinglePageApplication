<?php

namespace Vanguard\Watchers;

use Illuminate\Support\Facades\DB;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

trait UpdateFormulaTrait
{
    /**
     * @param Table $table
     * @param string $old
     * @param string $new
     * @return void
     */
    public function updTableFormulas(Table $table, string $old, string $new)
    {
        if ($table && $table->_fields_are_formula) {
            $dataQuery = new TableDataQuery($table);

            $fields_for_update = [];
            foreach ($table->_fields_are_formula as $header) {
                $fld = $dataQuery->getSqlFld($header->field . '_formula');
                $fields_for_update[$fld] = DB::raw('REPLACE(' . $fld . ', "' . $old . '", "' . $new . '")');

                $oldFixed = str_replace('\\', '', $old);
                $newFixed = str_replace('\\', '', $new);
                $header->update([
                    'f_formula' => str_replace($oldFixed, $newFixed, $header->f_formula),
                ]);
            }
            if ($fields_for_update) {
                $dataQuery->getQuery()->update($fields_for_update);
            }
        }
    }
}