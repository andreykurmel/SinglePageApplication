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
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('board_view_height')->default(100);
            $table->float('board_title_width')->default(0.30);
            $table->float('board_image_width')->default(0.30);
            $table->unsignedInteger('board_image_height')->default(30);
            $table->unsignedInteger('board_image_fld_id')->nullable();
            $table->string('board_display_position', 16)->nullable();
            $table->string('board_display_view', 16)->default('scroll');
            $table->string('board_display_fit', 16)->default('fill');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('board_view_height');
            $table->dropColumn('board_title_width');
            $table->dropColumn('board_image_width');
            $table->dropColumn('board_image_height');
            $table->dropColumn('board_image_fld_id');
            $table->dropColumn('board_display_position');
            $table->dropColumn('board_display_view');
            $table->dropColumn('board_display_fit');
        });
    }
}
