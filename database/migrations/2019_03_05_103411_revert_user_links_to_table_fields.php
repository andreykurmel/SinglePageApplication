<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RevertUserLinksToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->string('sel_link_type', 20)->nullable();
            $table->string('sel_link_icon', 20)->nullable();
            $table->string('sel_tooltip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('sel_link_type');
            $table->dropColumn('sel_link_icon');
            $table->dropColumn('sel_tooltip');
        });
    }
}
