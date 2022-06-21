<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RebuildingUserGroups1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('user_groups_2_table_column_groups');
        Schema::create('table_permissions_2_table_column_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_permission_id');
            $table->unsignedInteger('table_column_group_id');
            $table->tinyInteger('view')->default(0);
            $table->tinyInteger('edit')->default(0);
            $table->tinyInteger('shared')->default(0);

            $table->foreign('table_permission_id', 'tp_2_tcg_table_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('table_column_group_id', 'tp_2_tcg_table_column_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('table_permissions_2_table_column_groups');
        Schema::create('user_groups_2_table_column_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_column_group_id');
            $table->tinyInteger('view')->default(1);
            $table->tinyInteger('edit')->default(0);

            $table->foreign('user_group_id', 'user_groups_2_table_column_groups_groups_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_column_group_id', 'user_groups_2_table_column_groups_table_column_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('cascade');
        });
    }
}
