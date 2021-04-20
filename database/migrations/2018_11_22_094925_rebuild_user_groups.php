<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RebuildUserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('user_group_conditions');
        Schema::drop('user_groups_2_users');
        Schema::drop('user_groups');

        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 50);
            $table->string('notes')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('user_groups_2_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('user_id');

            $table->foreign('user_group_id', 'user_groups_2_users_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('user_id', 'user_groups_2_users_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('user_group_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->string('logic_operator', 20)->nullable();
            $table->string('user_field', 50);
            $table->string('compared_operator', 20);
            $table->string('compared_value', 50);

            $table->foreign('user_group_id', 'user_group_conditions_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('user_group_conditions');
        Schema::drop('user_groups_2_users');
        Schema::drop('user_groups');

        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('is_system')->default(0);
            $table->unsignedInteger('shared')->default(0);
            $table->string('name', 50);
            $table->string('notes')->nullable();
            $table->tinyInteger('can_add')->default(0);
            $table->tinyInteger('can_delete')->default(0);
            $table->string('can_download', 6)->default('000000');
            $table->tinyInteger('active')->default(0);
            $table->tinyInteger('user_history')->default(0);
            $table->tinyInteger('referencing')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('user_groups_2_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('user_id');

            $table->foreign('user_group_id', 'user_groups_2_users_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('user_id', 'user_groups_2_users_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('user_group_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->string('logic_operator', 20)->nullable();
            $table->string('user_field', 50);
            $table->string('compared_operator', 20);
            $table->string('compared_value', 50);

            $table->foreign('user_group_id', 'user_group_conditions_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }
}
