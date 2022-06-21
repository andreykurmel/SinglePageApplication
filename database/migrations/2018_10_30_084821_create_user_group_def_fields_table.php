<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupDefFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_def_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_field_id');
            $table->string('default', 255);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_group_id', 'user_group_def_fields_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'user_group_def_fields_table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('modified_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_def_fields');
    }
}
