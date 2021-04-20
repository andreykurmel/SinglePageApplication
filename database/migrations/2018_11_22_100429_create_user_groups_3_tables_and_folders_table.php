<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroups3TablesAndFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups_3_tables_and_folders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->enum('object_type', ['table','folder'])->default('table');
            $table->unsignedInteger('object_id');

            $table->foreign('user_group_id', 'user_groups_3_tables_and_folders_user_group_id')
                ->references('id')
                ->on('user_groups')
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
        Schema::dropIfExists('user_groups_3_tables_and_folders');
    }
}
