<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCpecShowingToTableFieldLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->string('spec_show', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->dropColumn('spec_show');
        });
    }
}
