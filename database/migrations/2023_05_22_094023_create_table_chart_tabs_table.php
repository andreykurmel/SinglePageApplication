<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableChartTabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_chart_tabs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('chart_data_range', 32)->default('0');
            $table->string('description', 255)->nullable();
            $table->string('chart_active', 16)->default('0');
            $table->unsignedInteger('bi_fix_layout')->default(1);
            $table->unsignedInteger('bi_can_add')->default(1);
            $table->unsignedInteger('bi_can_settings')->default(1);
            $table->unsignedInteger('bi_cell_height')->default(50);
            $table->unsignedInteger('bi_cell_spacing')->default(25);
            $table->unsignedInteger('bi_corner_radius')->default(5);

            $table->foreign('table_id', 'table_chart_tabs__table_id')
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
        Schema::dropIfExists('table_chart_tabs');
    }
}
