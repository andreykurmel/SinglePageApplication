<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSidePanelsToTableViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->string('side_top', 16)->default('');
            $table->string('side_left_menu', 16)->default('');
            $table->string('side_left_filter', 16)->default('');
            $table->string('side_right', 16)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->dropColumn('side_top');
            $table->dropColumn('side_left_menu');
            $table->dropColumn('side_left_filter');
            $table->dropColumn('side_right');
        });
    }
}
