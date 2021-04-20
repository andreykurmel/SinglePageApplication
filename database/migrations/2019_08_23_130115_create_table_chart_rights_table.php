<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChartRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_chart_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_chart_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_chart_id')
                ->references('id')
                ->on('table_charts')
                ->onDelete('cascade');

            $table->foreign('table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::dropIfExists('table_chart_rights');
    }
}
