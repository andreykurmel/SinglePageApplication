<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPubHiddenColForTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->tinyInteger('pub_hidden')->nullable();
            $table->tinyInteger('filters_on_top')->nullable();
            $table->string('filters_ontop_pos', 16)->default('start');
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
            $table->dropColumn('pub_hidden');
            $table->dropColumn('filters_on_top');
            $table->dropColumn('filters_ontop_pos');
        });
    }
}
