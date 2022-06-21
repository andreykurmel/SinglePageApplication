<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageOnBoardToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->unsignedInteger('is_image_on_board')->nullable();
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('board_view_height')->default(100);
            $table->unsignedInteger('board_image_width')->default(30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('is_image_on_board');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('board_view_height');
            $table->dropColumn('board_image_width');
        });
    }
}
