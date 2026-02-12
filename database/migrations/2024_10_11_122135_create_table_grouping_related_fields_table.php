<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGroupingRelatedFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_grouping_related_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grouping_id');
            $table->unsignedInteger('field_id');
            $table->tinyInteger('fld_visible')->default(0);
            $table->integer('fld_order')->default(0);

            $table->foreign('grouping_id', 'table_grouping_related_fields__grouping_id')->references('id')->on('table_groupings')->onDelete('cascade');
            $table->foreign('field_id', 'table_grouping_related_fields__field_id')->references('id')->on('table_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_grouping_related_fields');
    }
}
