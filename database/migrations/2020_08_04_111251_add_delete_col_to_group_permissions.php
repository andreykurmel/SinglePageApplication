<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteColToGroupPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions_2_table_column_groups', function (Blueprint $table) {
            $table->tinyInteger('delete')->default(0);
        });
        Schema::table('table_permissions_2_table_row_groups', function (Blueprint $table) {
            $table->tinyInteger('delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions_2_table_column_groups', function (Blueprint $table) {
            $table->dropColumn('delete');
        });
        Schema::table('table_permissions_2_table_row_groups', function (Blueprint $table) {
            $table->dropColumn('delete');
        });
    }
}
