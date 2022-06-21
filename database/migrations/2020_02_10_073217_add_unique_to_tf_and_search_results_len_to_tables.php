<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueToTfAndSearchResultsLenToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('search_results_len')->default(10);
        });

        Schema::table('table_fields', function (Blueprint $table) {
            $table->unsignedInteger('is_unique_collection')->default(0);
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
            $table->dropColumn('search_results_len');
        });

        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('is_unique_collection');
        });
    }
}
