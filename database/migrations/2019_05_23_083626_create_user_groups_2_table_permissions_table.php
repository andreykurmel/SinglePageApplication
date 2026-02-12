<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroups2TablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups_2_table_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('active')->default(0);

            $table->foreign('user_group_id', 'ug_2_tp__user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'ug_2_tp__table_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->unique(['user_group_id', 'table_permission_id'], 'ug_2_tp__unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_groups_2_table_permissions');
    }
}
