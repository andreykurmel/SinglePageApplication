<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersUGroupsRejectedTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_u_groups_rejected_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('table_id');

            $table->foreign('user_group_id', 'users_u_groups_rejected_tables__user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('user_id', 'users_u_groups_rejected_tables__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('table_id', 'users_u_groups_rejected_tables__table_id')
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
        Schema::dropIfExists('users_u_groups_rejected_tables');
    }
}
