<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDdlUnitConvFieldsToTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->tinyInteger('unit_conv_is_active')->default('0');
            $table->unsignedInteger('unit_conv_table_id')->nullable();

            $table->foreign('unit_conv_table_id', 'tables__unit_conv_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('set null');

            $table->unsignedInteger('unit_conv_from_fld_id')->nullable();
            $table->unsignedInteger('unit_conv_to_fld_id')->nullable();
            $table->unsignedInteger('unit_conv_operator_fld_id')->nullable();
            $table->unsignedInteger('unit_conv_factor_fld_id')->nullable();
            $table->unsignedInteger('unit_conv_formula_fld_id')->nullable();
            $table->unsignedInteger('unit_conv_formula_reverse_fld_id')->nullable();

            $table->foreign('unit_conv_from_fld_id', 'tables__unit_conv_from_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('unit_conv_to_fld_id', 'tables__unit_conv_to_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('unit_conv_operator_fld_id', 'tables__unit_conv_operator_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('unit_conv_factor_fld_id', 'tables__unit_conv_factor_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('unit_conv_formula_fld_id', 'tables__unit_conv_formula_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('unit_conv_formula_reverse_fld_id', 'tables__unit_conv_formula_reverse_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign('tables__unit_conv_table_id');
            $table->dropForeign('tables__unit_conv_from_fld_id');
            $table->dropForeign('tables__unit_conv_to_fld_id');
            $table->dropForeign('tables__unit_conv_operator_fld_id');
            $table->dropForeign('tables__unit_conv_factor_fld_id');
            $table->dropForeign('tables__unit_conv_formula_fld_id');
            $table->dropForeign('tables__unit_conv_formula_reverse_fld_id');

            $table->dropColumn('unit_conv_is_active');
            $table->dropColumn('unit_conv_table_id');
            $table->dropColumn('unit_conv_from_fld_id');
            $table->dropColumn('unit_conv_to_fld_id');
            $table->dropColumn('unit_conv_operator_fld_id');
            $table->dropColumn('unit_conv_factor_fld_id');
            $table->dropColumn('unit_conv_formula_fld_id');
            $table->dropColumn('unit_conv_formula_reverse_fld_id');
        });
    }
}
