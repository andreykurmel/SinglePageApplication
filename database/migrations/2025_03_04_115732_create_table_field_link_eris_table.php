<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkErisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_eri_parts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_id');
            $table->string('part', 128);
            $table->string('type', 16);
            $table->string('section_q_identifier', 128);
            $table->string('section_r_identifier', 128);
            $table->unsignedInteger('row_order')->default(0);

            $table->foreign('table_link_id', 'tflep__table_link_id')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');
        });

        Schema::create('table_field_link_eri_part_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_eri_part_id');
            $table->string('variable_name', 128);
            $table->string('var_notes', 255);
            $table->unsignedInteger('row_order')->default(0);

            $table->foreign('table_link_eri_part_id', 'tflepv__table_link_eri_part_id')
                ->references('id')
                ->on('table_field_link_eri_parts')
                ->onDelete('cascade');
        });



        Schema::create('table_field_link_eri_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_id');
            $table->unsignedInteger('eri_part_id')->nullable();
            $table->unsignedInteger('eri_table_id');
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('row_order')->default(0);

            $table->foreign('table_link_id', 'tflet__table_link_id')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');
            $table->foreign('eri_table_id', 'tflet__eri_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
            $table->foreign('eri_part_id', 'tflet__eri_part_id')
                ->references('id')
                ->on('table_field_link_eri_parts')
                ->onDelete('set null');
        });

        Schema::create('table_field_link_eri_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_eri_id');
            $table->string('eri_variable', 128);
            $table->unsignedInteger('eri_field_id');
            $table->unsignedInteger('eri_master_field_id');
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('row_order')->default(0);

            $table->foreign('table_link_eri_id', 'tflef__table_link_eri_id')
                ->references('id')
                ->on('table_field_link_eri_tables')
                ->onDelete('cascade');
            $table->foreign('eri_field_id', 'tflef__eri_field_id')
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
        Schema::dropIfExists('table_field_link_eri_fields');
        Schema::dropIfExists('table_field_link_eri_tables');
        Schema::dropIfExists('table_field_link_eri_part_variables');
        Schema::dropIfExists('table_field_link_eri_parts');
    }
}
