<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMirrorDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mirror_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('row_id');
            $table->string('mirror_row_ids', 255)->nullable();
            $table->timestamps();

            $table->foreign('table_id', 'mirror_datas__table_id')
                ->references('id')
                ->on('tables')
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
        Schema::dropIfExists('mirror_datas');
    }
}
