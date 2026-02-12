<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadingFileFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploading_file_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category', 128);
            $table->string('format', 64);
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
        Schema::dropIfExists('uploading_file_formats');
    }
}
