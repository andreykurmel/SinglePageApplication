<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableViewFilteringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_view_filtering', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_view_id');
            $table->unsignedInteger('field_id');
            $table->tinyInteger('active')->nullable();
            $table->tinyInteger('input_only')->nullable();
            $table->string('criteria', 32);

            $table->foreign('table_view_id', 'table_view_filtering__table_view_id')
                ->references('id')
                ->on('table_views')
                ->onDelete('cascade');

            $table->foreign('field_id', 'table_view_filtering__field_id')
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
        Schema::dropIfExists('table_view_filtering');
    }
}
