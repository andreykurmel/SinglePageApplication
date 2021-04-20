<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_views', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_id');
            $table->string('hash', 50);
            $table->string('name', 50)->nullable();
            $table->string('notes')->nullable();

            $table->foreign('folder_id', 'folder_views_folder_id')
                ->references('id')
                ->on('folders')
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
        Schema::dropIfExists('folder_views');
    }
}
