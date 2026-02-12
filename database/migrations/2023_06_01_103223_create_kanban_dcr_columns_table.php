<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanDcrColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_data_requests_2_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_data_requests_id');
            $table->unsignedInteger('table_field_id');
            $table->string('width_of_table_popup', 16)->default('full');
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->unsignedInteger('is_table_field_in_popup')->default(0);
            $table->unsignedInteger('is_hdr_lvl_one_row')->default(0);
            $table->tinyInteger('is_dcr_section')->default(0);
            $table->string('dcr_section_name', 64)->nullable();
            $table->tinyInteger('fld_popup_shown')->default(1);
            $table->tinyInteger('fld_display_name')->default(1);
            $table->tinyInteger('fld_display_value')->default(1);
            $table->tinyInteger('fld_display_border')->default(1);
            $table->string('fld_display_header_type', 32)->default('default');
            $table->tinyInteger('is_topbot_in_popup')->default(0);

            $table->foreign('table_data_requests_id', 'tdrcs__table_data_requests')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'tdrcs__table_field_id')
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
