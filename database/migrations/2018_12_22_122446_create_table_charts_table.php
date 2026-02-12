<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_charts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128)->nullable();
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('col_idx')->default(0);
            $table->unsignedInteger('row_idx')->default(0);
            $table->text('chart_settings');

            $table->foreign('table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('table_charts');
    }
}
