<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBiSettingsToTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('bi_fix_layout')->default(1);
            $table->unsignedInteger('bi_can_add')->default(1);
            $table->unsignedInteger('bi_can_settings')->default(1);
            $table->unsignedInteger('bi_cell_height')->default(50);
            $table->unsignedInteger('bi_cell_spacing')->default(25);
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
            $table->dropColumn('bi_fix_layout');
            $table->dropColumn('bi_can_add');
            $table->dropColumn('bi_can_settings');
            $table->dropColumn('bi_cell_height');
            $table->dropColumn('bi_cell_spacing');
        });
    }
}
