<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RebuildingUserGroups3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('user_group_def_fields');
        Schema::create('table_permission_def_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_permission_id');
            $table->unsignedInteger('table_field_id');
            $table->string('default', 1024);

            $table->foreign('table_permission_id', 'table_permission_def_fields_table_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_permission_def_fields_table_field_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::drop('table_permission_def_fields');
        Schema::create('user_group_def_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_field_id');

            $table->foreign('user_group_id', 'user_group_def_fields_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'user_group_def_fields_table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });
    }
}
