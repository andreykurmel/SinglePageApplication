<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCharedToRowsAndColumnsGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_groups_2_table_column_groups', function (Blueprint $table) {
            $table->tinyInteger('shared')->default(0);
        });
        Schema::table('user_groups_2_table_row_groups', function (Blueprint $table) {
            $table->tinyInteger('shared')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_groups_2_table_column_groups', function (Blueprint $table) {
            $table->dropColumn('shared');
        });
        Schema::table('user_groups_2_table_row_groups', function (Blueprint $table) {
            $table->dropColumn('shared');
        });
    }
}
