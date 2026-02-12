<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPopupDisplayToTableFieldLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->tinyInteger('popup_can_table')->default(0);
            $table->tinyInteger('popup_can_list')->default(1);
            $table->tinyInteger('popup_can_board')->default(0);
            $table->string('popup_display', 32)->nullable();
            $table->unsignedInteger('pop_width_px')->default(768);
            $table->unsignedInteger('pop_width_px_min')->default(200);
            $table->unsignedInteger('pop_width_px_max')->nullable();
            $table->unsignedInteger('pop_height')->default(80);
            $table->unsignedInteger('pop_height_min')->default(20);
            $table->unsignedInteger('pop_height_max')->nullable();
            $table->string('listing_panel_status', 16)->default('open');
            $table->float('listing_header_wi')->default(0.35);
            $table->unsignedInteger('multiple_web_label_fld_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropColumn('popup_can_table');
            $table->dropColumn('popup_can_list');
            $table->dropColumn('popup_can_board');
            $table->dropColumn('popup_display');
            $table->dropColumn('pop_width_px');
            $table->dropColumn('pop_width_px_min');
            $table->dropColumn('pop_width_px_max');
            $table->dropColumn('pop_height');
            $table->dropColumn('pop_height_min');
            $table->dropColumn('pop_height_max');
            $table->dropColumn('listing_panel_status');
            $table->dropColumn('listing_header_wi');
            $table->dropColumn('multiple_web_label_fld_id');
        });
    }
}
