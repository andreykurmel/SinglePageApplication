<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StartRebuildingUserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('user_groups_2_cond_formats');
        Schema::create('table_permissions_2_cond_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_permission_id')->nullable();
            $table->unsignedInteger('cond_format_id');

            $table->foreign('table_permission_id', 'table_permissions_2_cond_formats_table_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('cond_format_id', 'table_permissions_2_cond_formats_cond_format_id')
                ->references('id')
                ->on('cond_formats')
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
        Schema::drop('table_permissions_2_cond_formats');
        Schema::create('user_groups_2_cond_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('cond_format_id');

            $table->foreign('user_group_id', 'user_groups_2_cond_formats_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('cond_format_id', 'user_groups_2_cond_formats_cond_format_id')
                ->references('id')
                ->on('cond_formats')
                ->onDelete('cascade');
        });
    }
}
