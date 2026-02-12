<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableGroupingStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_grouping_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grouping_id');
            $table->unsignedInteger('field_id');
            $table->string('stat_fn', 32)->nullable();
            $table->tinyInteger('rg_active')->default(1);

            $table->foreign('grouping_id', 'table_grouping_stats__grouping_id')
                ->references('id')
                ->on('table_groupings')
                ->onDelete('cascade');
            $table->foreign('field_id', 'table_grouping_stats__field_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::dropIfExists('table_grouping_stats');
    }
}
