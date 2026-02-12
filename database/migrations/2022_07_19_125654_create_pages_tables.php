<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 128);

            $table->unsignedInteger('cell_spacing')->default(10);
            $table->unsignedInteger('edge_spacing')->default(10);
            $table->unsignedInteger('border_width')->default(7);
            $table->unsignedInteger('border_radius')->default(10);
            $table->string('share_hash', 64)->default('');

            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id', 'pages__user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('page_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('page_id');
            $table->string('name', 128)->nullable();
            $table->string('type', 50);
            $table->string('view_part', 64)->nullable();
            $table->unsignedInteger('table_id')->nullable();
            $table->unsignedInteger('table_view_id')->nullable();
            $table->string('row_hash', 32);
            $table->string('grid_position', 255);

            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('page_id', 'page_contents__page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->foreign('table_id', 'page_contents__table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('table_view_id', 'page_contents__table_view_id')->references('id')->on('table_views')->onDelete('cascade');
        });

        Schema::create('folders_2_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->string('entity_type', 50);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('folder_id')->nullable();
            $table->string('structure', 50)->default('private');

            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id', 'folders_2_entities__user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('folder_id', 'folders_2_entities__folder_id')->references('id')->on('folders')->onDelete('cascade');

            $table->index('entity_type');
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
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('folders_2_entities');
    }
}
