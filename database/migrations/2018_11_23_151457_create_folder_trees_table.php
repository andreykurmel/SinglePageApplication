<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_trees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('element_id');
            $table->unsignedInteger('child_id');
            $table->unsignedInteger('parent_id')->default(0);
            $table->unsignedInteger('level')->default(0);

            $table->foreign('element_id', 'folder_trees_element_id')
                ->references('id')
                ->on('folders')
                ->onDelete('cascade');

            $table->foreign('child_id', 'folder_trees_child_id')
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
        Schema::dropIfExists('folder_trees');
    }
}
