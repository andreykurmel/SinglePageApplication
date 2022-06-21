<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPropToUnitConv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->string('property', 64)->nullable();
            $table->string('formula', 255)->nullable();
            $table->string('formula_formula', 255)->nullable();
            $table->string('formula_reverse', 255)->nullable();
            $table->string('formula_reverse_formula', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->dropColumn('property');
            $table->dropColumn('formula');
            $table->dropColumn('formula_formula');
            $table->dropColumn('formula_reverse');
            $table->dropColumn('formula_reverse_formula');
        });
    }
}
