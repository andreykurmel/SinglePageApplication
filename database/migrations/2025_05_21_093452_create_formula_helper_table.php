<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormulaHelperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formula_helpers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('formula', 255);
            $table->text('notes')->nullable();
            $table->string('row_hash', 64)->nullable();
            $table->unsignedInteger('row_order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formula_helpers');
    }
}
