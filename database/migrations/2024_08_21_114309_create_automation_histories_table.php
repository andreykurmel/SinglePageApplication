<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('table_id');
            $table->string('function', 50);
            $table->string('name', 255);
            $table->string('component', 50);
            $table->string('part', 50);
            $table->string('exec_time', 50);
            $table->dateTime('start_time');
            $table->integer('year');
            $table->integer('month');
            $table->integer('week');
            $table->integer('day');

            $table->string('row_hash', 32)->nullable();
            $table->integer('row_order')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id', 'automation_histories__user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('table_id', 'automation_histories__table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automation_histories');
    }
}
