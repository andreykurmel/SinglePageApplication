<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkErisMoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_eri_field_conversions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_eri_field_id');
            $table->string('eri_convers', 255);
            $table->string('tablda_convers', 255);

            $table->foreign('table_link_eri_field_id', 'tflefc__table_link_eri_field_id')
                ->references('id')
                ->on('table_field_link_eri_fields')
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
        Schema::dropIfExists('table_field_link_eri_field_conversions');
    }
}
