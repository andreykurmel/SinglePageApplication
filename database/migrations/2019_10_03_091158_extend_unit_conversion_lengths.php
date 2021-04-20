<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendUnitConversionLengths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->string('from_unit', 512)->change();
            $table->string('to_unit', 512)->change();
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
            $table->string('from_unit', 25)->change();
            $table->string('to_unit', 25)->change();
        });
    }
}
