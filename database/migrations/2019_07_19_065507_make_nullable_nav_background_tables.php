<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNullableNavBackgroundTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('nav_background');
            $table->dropColumn('autoload_new_data');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->string('nav_background', 10)->nullable();
            $table->tinyInteger('autoload_new_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
