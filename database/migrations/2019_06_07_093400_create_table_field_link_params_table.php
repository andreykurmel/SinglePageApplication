<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_params', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_field_link_id');
            $table->string('param');
            $table->string('value')->nullable();
            $table->unsignedInteger('column_id')->nullable();

            $table->foreign('table_field_link_id', 'tflp__table_field_link')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');

            $table->foreign('column_id', 'tflp__column_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::dropIfExists('table_field_link_params');
    }
}
