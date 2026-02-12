<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSrvColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_srv_2_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('table_field_id');
            $table->string('width_of_table_popup', 16)->default('full');
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->unsignedInteger('is_table_field_in_popup')->default(0);
            $table->unsignedInteger('is_hdr_lvl_one_row')->default(0);
            $table->tinyInteger('is_dcr_section')->default(0);

            $table->foreign('table_id', 'tsrv__table_data_requests')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'tsrv__table_field_id')
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
        Schema::dropIfExists('table_kanban_rights');
    }
}
