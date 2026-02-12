<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableGroupingFieldStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_grouping_field_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grouping_field_id');
            $table->unsignedInteger('field_id');
            $table->string('stat_fn', 32)->nullable();
            $table->tinyInteger('rg_active')->default(1);

            $table->foreign('grouping_field_id', 'tgfs__grouping_field_id')
                ->references('id')
                ->on('table_grouping_fields')
                ->onDelete('cascade');
            $table->foreign('field_id', 'tgfs__field_id')
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
        Schema::dropIfExists('table_grouping_field_stats');
    }
}
