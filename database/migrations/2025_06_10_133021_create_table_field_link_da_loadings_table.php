<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkDaLoadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_da_loadings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_id');
            $table->string('column_key', 64)->nullable();
            $table->unsignedInteger('da_field_id');
            $table->unsignedInteger('da_master_field_id')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('row_order')->default(0);

            $table->foreign('table_link_id', 'tfldl__table_link_id')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');
            $table->foreign('da_field_id', 'tfldl__da_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
            $table->foreign('da_master_field_id', 'tfldl__da_master_field_id')
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
        Schema::dropIfExists('table_field_link_da_loadings');
    }
}
