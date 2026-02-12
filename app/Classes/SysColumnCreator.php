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
     * Watch for InputType and add/drop Sys Col.
     *
     * @param Table $table
     * @param TableField $header
     */
    public function watchInputType(Table $table, TableField $header)
    {
        $fld_name = $header->field.'_formula';
        if ( $header->input_type === 'Formula' ) {
            $this->addSysCol($table, $fld_name, $header->field, $header->f_default ?: '', true);
        } else {
            $this->dropSysCol($table, $fld_name, $header->field, true);
        }

        $fld_name = $header->field.'_mirror';
        if ( $header->input_type === 'Mirror' ) {
            $this->addSysCol($table, $fld_name, $header->field);
        } else {
            $this->dropSysCol($table, $fld_name, $header->field);
        }
    }

    /**
     * @param Table $table
     * @param string $field_formula
     * @param string $field
     * @param string $default
     * @param bool $changeData
     * @return void
     */
    protected function addSysCol(Table $table, string $field_formula, string $field, string $default = '', bool $changeData = false)
    {
        if (
            !$this->schema($table)->hasColumn($table->db_name, $field_formula)
        ) {
            //add column
            $this->schema($table)
                ->table($table->db_name, function (Blueprint $table) use ($field_formula, $default) {
                    $table->string($field_formula, 255)
                        ->default(addslashes($default ?: ''))
                        ->nullable();
                });

            if ($changeData) {
                //change data in rows
                $this->db($table)->table($table->db_name)->update([
                    $field_formula => DB::raw($field)
                ]);
                $this->db($table)->table($table->db_name)->update([
                    $field => ''
                ]);
            }
        }
    }

    /**
     * @param Table $table
     * @param string $field_formula
     * @param string $field
     * @param bool $changeData
     * @return void
     */
    protected function dropSysCol(Table $table, string $field_formula, string $field, bool $changeData = false)
    {
        if (
            $this->schema($table)->hasColumn($table->db_name, $field_formula)
        ) {
            if ($changeData) {
                //change data in rows
                $this->db($table)->table($table->db_name)->update([
                    $field => DB::raw($field_formula)
                ]);
            }

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