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
            $table->tinyInteger('unit_conv_by_user')->default(0);
            $table->tinyInteger('unit_conv_by_system')->default(1);
            $table->tinyInteger('unit_conv_by_lib')->default(1);

            $table->tinyInteger('kanban_form_table')->default(1);
            $table->unsignedInteger('kanban_card_width')->default(300);
            $table->unsignedInteger('kanban_card_height')->nullable();
            $table->string('kanban_sort_type', 16)->nullable();
            $table->string('kanban_header_color', 16)->default('#DDD');
            $table->tinyInteger('kanban_hide_empty_tab')->default(1);
            $table->string('kanban_picture_style', 16)->default('scroll');

            $table->string('gantt_info_type', 16)->nullable();
            $table->unsignedInteger('gantt_navigation')->nullable();
            $table->unsignedInteger('gantt_show_names')->nullable();
            $table->unsignedInteger('gantt_highlight')->nullable();
            $table->string('gantt_day_format', 16)->nullable();

            $table->string('calendar_locale', 32)->nullable();
            $table->string('calendar_timezone', 32)->nullable();
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
            $table->dropColumn('kanban_card_width');
            $table->dropColumn('kanban_card_height');
            $table->dropColumn('kanban_sort_type');
            $table->dropColumn('kanban_header_color');
            $table->dropColumn('kanban_hide_empty_tab');
            $table->dropColumn('kanban_picture_style');

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
