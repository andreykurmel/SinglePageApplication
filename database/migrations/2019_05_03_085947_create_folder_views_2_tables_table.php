<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderViews2TablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_views_2_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('folder_view_id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('assigned_view_id')->nullable();

            $table->foreign('folder_view_id', 'folder_views_2_tables_folder_view_id')
                ->references('id')
                ->on('folder_views')
                ->onDelete('cascade');

            $table->foreign('table_id', 'folder_views_2_tables_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('assigned_view_id', 'folder_views_2_tables_view_id')
                ->references('id')
                ->on('table_views')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folder_views_2_tables');
    }
}
