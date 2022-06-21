<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroups2CondFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups_2_cond_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('cond_format_id');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_group_id')->references('id')->on('user_groups')->onDelete('cascade');
            $table->foreign('cond_format_id')->references('id')->on('cond_formats')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['user_group_id', 'cond_format_id'], 'user_groups_2_cond_formats_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_groups_2_cond_formats');
    }
}
