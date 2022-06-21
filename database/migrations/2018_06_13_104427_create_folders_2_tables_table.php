<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolders2TablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders_2_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('folder_id')->nullable();
            $table->unsignedInteger('is_folder_link')->nullable();
            $table->string('type', 50)->default('table');
            $table->string('structure', 50)->default('private');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('type');
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
        Schema::dropIfExists('folders_2_tables');
    }
}
