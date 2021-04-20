<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDcrFormSettingsFieldsToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->integer('dcr_title_height')->nullable();
            $table->integer('dcr_title_font_size')->nullable();
            $table->string('dcr_title_font_color', 16)->nullable();
            $table->string('dcr_title_bg_img', 255)->nullable();
            $table->string('dcr_title_bg_fit', 16)->nullable();
            $table->string('dcr_title_bg_color', 16)->nullable();
            $table->integer('dcr_form_width')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropColumn('dcr_title_height');
            $table->dropColumn('dcr_title_font_size');
            $table->dropColumn('dcr_title_font_color');
            $table->dropColumn('dcr_title_bg_img');
            $table->dropColumn('dcr_title_bg_fit');
            $table->dropColumn('dcr_title_bg_color');
            $table->dropColumn('dcr_form_width');
        });
    }
}
