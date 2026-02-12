<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChartTabCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_charts', function (Blueprint $table) {
            $table->unsignedInteger('table_chart_tab_id')->nullable();

            $table->foreign('table_chart_tab_id', 'table_charts__table_chart_tab_id')
                ->references('id')
                ->on('table_chart_tabs')
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
        Schema::table('table_charts', function (Blueprint $table) {
            $table->dropForeign('table_charts__table_chart_tab_id');
            $table->dropColumn('table_chart_tab_id');
        });
    }
}
