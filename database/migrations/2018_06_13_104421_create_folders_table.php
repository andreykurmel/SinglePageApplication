<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('structure', 50)->default('private');
            $table->tinyInteger('is_opened')->default(1);
            $table->tinyInteger('is_system')->default(0);

            $table->string('import_source', 64)->nullable();
            $table->string('importfolder_airtable_save', 255)->nullable();
            $table->string('importfolder_google_save', 255)->nullable();
            $table->string('importfolder_dropbox_save', 255)->nullable();
            $table->string('importfolder_onedrive_save', 255)->nullable();
            $table->string('importfolder_ocr_save', 255)->nullable();
            $table->string('importfolder_local_save', 255)->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('structure');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders');
    }
}
