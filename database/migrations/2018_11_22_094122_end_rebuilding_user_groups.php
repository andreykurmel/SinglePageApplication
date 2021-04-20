<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EndRebuildingUserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('user_group_sub_trees');
        Schema::create('folder_permissions_2_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_permission_id');
            $table->unsignedInteger('table_id');

            $table->foreign('folder_permission_id', 'folder_permissions_2_tables_folder_permission_id')
                ->references('id')
                ->on('folder_permissions')
                ->onDelete('cascade');

            $table->foreign('table_id', 'folder_permissions_2_tables_table_id')
                ->references('id')
                ->on('tables')
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
        Schema::drop('folder_permissions_2_tables');
        Schema::create('user_group_sub_trees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_id');

            $table->foreign('user_group_id', 'user_group_sub_trees_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_id', 'user_group_sub_trees_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });
    }
}
