<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreSettingsToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->renameColumn('dcr_title_line_top', 'dcr_form_line_top');
            $table->renameColumn('dcr_title_line_bot', 'dcr_form_line_bot');
            $table->renameColumn('dcr_title_line_thick', 'dcr_form_line_thick');
            $table->renameColumn('dcr_line_color', 'dcr_form_line_color');
            $table->renameColumn('dcr_message', 'dcr_form_message');
            $table->renameColumn('dcr_message_font', 'dcr_form_message_font');
            $table->renameColumn('dcr_message_size', 'dcr_form_message_size');
            $table->renameColumn('dcr_message_color', 'dcr_form_message_color');
            $table->renameColumn('dcr_shadow_form_color', 'dcr_form_shadow_color');
            $table->renameColumn('dcr_message_style', 'dcr_form_message_style');

            $table->tinyInteger('dcr_sec_line_top')->default(1);
            $table->tinyInteger('dcr_sec_line_bot')->default(1);
            $table->string('dcr_sec_line_color', 16)->nullable();
            $table->unsignedInteger('dcr_sec_line_thick')->default(1);
            $table->string('dcr_sec_bg_top', 16)->nullable();
            $table->string('dcr_sec_bg_bot', 16)->nullable();
            $table->string('dcr_sec_bg_img', 255)->nullable();
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
            $table->dropColumn('dcr_sec_line_top');
            $table->dropColumn('dcr_sec_line_bot');
            $table->dropColumn('dcr_sec_line_color', 16);
            $table->dropColumn('dcr_sec_line_thick');
            $table->dropColumn('dcr_sec_bg_top', 16);
            $table->dropColumn('dcr_sec_bg_bot', 16);
            $table->dropColumn('dcr_sec_bg_img', 255);

            $table->renameColumn('dcr_form_line_top', 'dcr_title_line_top');
            $table->renameColumn('dcr_form_line_bot', 'dcr_title_line_bot');
            $table->renameColumn('dcr_form_line_thick', 'dcr_title_line_thick');
            $table->renameColumn('dcr_form_line_color', 'dcr_line_color');
            $table->renameColumn('dcr_form_message', 'dcr_message');
            $table->renameColumn('dcr_form_message_font', 'dcr_message_font');
            $table->renameColumn('dcr_form_message_size', 'dcr_message_size');
            $table->renameColumn('dcr_form_message_color', 'dcr_message_color');
            $table->renameColumn('dcr_form_shadow_color', 'dcr_shadow_form_color');
            $table->renameColumn('dcr_form_message_style', 'dcr_message_style');
        });
    }
}
