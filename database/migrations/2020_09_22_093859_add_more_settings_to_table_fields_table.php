<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreSettingsToTableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('map_popup_width')->default(450);
            $table->unsignedInteger('map_popup_height')->default(300);
            $table->string('map_popup_header_color', 16)->default('#DDD');
            $table->string('map_picture_style', 16)->default('scroll');
            $table->unsignedInteger('map_picture_field')->nullable();
            $table->float('map_picture_width')->default('30');

            $table->tinyInteger('unit_conv_by_user')->default(0);
            $table->tinyInteger('unit_conv_by_system')->default(1);
            $table->tinyInteger('unit_conv_by_lib')->default(1);

            $table->tinyInteger('kanban_form_table')->default(1);
            $table->tinyInteger('kanban_center_align')->nullable();
            $table->unsignedInteger('kanban_card_width')->default(300);
            $table->unsignedInteger('kanban_card_height')->nullable();
            $table->string('kanban_sort_type', 16)->nullable();
            $table->string('kanban_header_color', 16)->default('#DDD');
            $table->tinyInteger('kanban_hide_empty_tab')->default(1);
            $table->unsignedInteger('kanban_picture_field')->nullable();
            $table->float('kanban_picture_width')->default('30');

            $table->string('gantt_info_type', 16)->nullable();
            $table->unsignedInteger('gantt_navigation')->nullable();
            $table->unsignedInteger('gantt_show_names')->nullable();
            $table->unsignedInteger('gantt_highlight')->nullable();
            $table->string('gantt_day_format', 16)->nullable();

            $table->string('calendar_locale', 32)->nullable();
            $table->string('calendar_timezone', 32)->nullable();

            $table->string('import_web_scrap_save', 512)->nullable();
            $table->string('import_gsheet_save', 255)->nullable();
            $table->string('import_airtable_save', 255)->nullable();
            $table->string('import_csv_save', 512)->nullable();
            $table->string('import_mysql_save', 255)->nullable();
            $table->string('import_paste_save', 1024)->nullable();
            $table->string('import_table_ocr_save', 255)->nullable();
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
            $table->dropColumn('unit_conv_by_user');
            $table->dropColumn('unit_conv_by_system');
            $table->dropColumn('unit_conv_by_lib');

            $table->dropColumn('kanban_form_table');
            $table->dropColumn('kanban_center_align');
            $table->dropColumn('kanban_card_width');
            $table->dropColumn('kanban_card_height');
            $table->dropColumn('kanban_sort_type');
            $table->dropColumn('kanban_header_color');
            $table->dropColumn('kanban_hide_empty_tab');

            $table->dropColumn('gantt_info_type');
            $table->dropColumn('gantt_navigation');
            $table->dropColumn('gantt_show_names');
            $table->dropColumn('gantt_highlight');
            $table->dropColumn('gantt_day_format');

            $table->dropColumn('calendar_locale');
            $table->dropColumn('calendar_timezone');
        });
    }
}
