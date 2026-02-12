<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEriPartActivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_eri_part_actives', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('link_id');
            $table->unsignedInteger('eri_part_id');
            $table->unsignedInteger('row_id');
            $table->tinyInteger('parser_active')->default(1);
            $table->tinyInteger('writer_active')->default(1);

            $table->foreign('link_id', 'tflepa__link_id')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');
            $table->foreign('eri_part_id', 'tflepa__eri_part_id')
                ->references('id')
                ->on('table_field_link_eri_parts')
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
        Schema::dropIfExists('table_field_link_eri_part_actives');
    }
}
