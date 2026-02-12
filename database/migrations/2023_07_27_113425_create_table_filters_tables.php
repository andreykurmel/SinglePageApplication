<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFiltersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_saved_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('related_colgroup_id')->nullable();
            $table->string('name', 255);
            $table->json('filters_object');

            $table->foreign('table_id', 'table_saved_filters__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('user_id', 'table_saved_filters__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('related_colgroup_id', 'table_saved_filters__related_colgroup_id')
                ->references('id')
                ->on('table_column_groups')
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
        Schema::dropIfExists('table_saved_filters');
    }
}
