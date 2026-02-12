<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDdlReferenceColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ddl_reference_colors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ddl_reference_id');
            $table->string('ref_value', 64);
            $table->string('color', 16)->default('');
            $table->string('image_ref_path', 255)->nullable();
            $table->unsignedInteger('max_selections')->nullable();

            $table->foreign('ddl_reference_id', 'ddl_reference_colors_ddl_reference_id')
                ->references('id')
                ->on('ddl_references')
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
        Schema::dropIfExists('ddl_reference_colors');
    }
}
