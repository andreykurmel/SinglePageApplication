<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSidesToFolderViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folder_views', function (Blueprint $table) {
            $table->string('side_top', 16)->default('');
            $table->string('side_left_menu', 16)->default('');
            $table->string('side_left_filter', 16)->default('');
            $table->string('side_right', 16)->default('');
            $table->unsignedInteger('def_table_id')->nullable();

            $table->foreign('def_table_id', 'folder_views__def_table_id')
                ->references('id')
                ->on('tables')
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
        Schema::table('folder_views', function (Blueprint $table) {
            $table->dropForeign('folder_views__def_table_id');

            $table->dropColumn('def_table_id');
            $table->dropColumn('side_top');
            $table->dropColumn('side_left_menu');
            $table->dropColumn('side_left_filter');
            $table->dropColumn('side_right');
        });
    }
}
