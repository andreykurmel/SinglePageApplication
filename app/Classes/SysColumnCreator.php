<?php

namespace Vanguard\Classes;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Services\Tablda\HelperService;

class SysColumnCreator
{
    /**
     * Should Have Sys Col.
     *
     * @param TableField $header
     * @return bool
     */
    protected function shouldHave(TableField $header)
    {
        return $header->input_type === 'Formula';
    }

    /**
     * Watch for InputType and add/drop Sys Col.
     *
     * @param Table $table
     * @param TableField $header
     */
    public function watchInputType(Table $table, TableField $header)
    {
        $fld_name = $header->field.'_formula';
        if ( $this->shouldHave($header) ) {
            $this->addSysCol($table, $fld_name, $header);
        } else {
            $this->dropSysCol($table, $fld_name, $header);
        }
    }

    /**
     * @param Table $table
     * @param string $field_formula
     * @param TableField $header
     */
    protected function addSysCol(Table $table, string $field_formula, TableField $header)
    {
        if (
            !$this->schema($table)->hasColumn($table->db_name, $field_formula)
        ) {
            //add column
            $this->schema($table)
                ->table($table->db_name, function (Blueprint $table) use ($field_formula, $header) {
                    $table->string($field_formula, 255)
                        ->default(addslashes($header->f_formula ?: ''))
                        ->nullable();
                });

            //change data in rows
            $this->db($table)->table($table->db_name)->update([
                $field_formula => DB::raw($header->field)
            ]);
            $this->db($table)->table($table->db_name)->update([
                $header->field => ''
            ]);
        }
    }

    /**
     * @param Table $table
     * @param string $field_formula
     * @param TableField $header
     */
    protected function dropSysCol(Table $table, string $field_formula, TableField $header)
    {
        if (
            $this->schema($table)->hasColumn($table->db_name, $field_formula)
        ) {
            //change data in rows
            $this->db($table)->table($table->db_name)->update([
                $header->field => DB::raw($field_formula)
            ]);

            //drop column
            $this->schema($table)
                ->table($table->db_name, function (Blueprint $table) use ($field_formula) {
                    $table->dropColumn($field_formula);
                });
        }
    }

    /**
     * @param Table $table
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema(Table $table)
    {
        return Schema::connection( (!$table->is_system ? 'mysql_data' : null) );
    }

    /**
     * @param Table $table
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
     */
    protected function db(Table $table)
    {
        return (new HelperService())->getConnectionForTable($table);
    }
}