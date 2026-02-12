<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemoteFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remote_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('row_id');
            $table->string('remote_link', 512);
            $table->string('filename', 255);
            $table->tinyInteger('is_img')->default(0);
            $table->tinyInteger('is_video')->default(0);
            $table->tinyInteger('is_audio')->default(0);
            $table->tinyInteger('can_upload')->default(0);
            $table->string('special_mark', 32)->nullable();
            $table->string('special_content', 255)->nullable();
            $table->string('cloud_meta', 255)->nullable();
            $table->string('local_thumb', 255)->nullable();
            $table->string('filehash', 64)->nullable();
            $table->string('notes', 255)->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('remote_files');
    }
}
