<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGanttSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_gantt_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_parent_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_main_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_name')->nullable();
            $table->unsignedTinyInteger('is_gantt_parent')->nullable();
            $table->unsignedTinyInteger('is_gantt_start')->nullable();
            $table->unsignedTinyInteger('is_gantt_end')->nullable();
            $table->unsignedTinyInteger('is_gantt_progress')->nullable();
            $table->unsignedTinyInteger('is_gantt_color')->nullable();
            $table->unsignedTinyInteger('is_gantt_tooltip')->nullable();
            $table->unsignedTinyInteger('is_gantt_left_header')->nullable();
            $table->unsignedTinyInteger('is_gantt_label_symbol')->nullable();
            $table->unsignedTinyInteger('is_gantt_milestone')->nullable();

            $table->unsignedTinyInteger('is_calendar_start')->nullable();
            $table->unsignedTinyInteger('is_calendar_end')->nullable();
            $table->unsignedTinyInteger('is_calendar_title')->nullable();
            $table->unsignedTinyInteger('is_calendar_cond_format')->nullable();
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
            $table->dropColumn('is_gantt_group');
            $table->dropColumn('is_gantt_parent_group');
            $table->dropColumn('is_gantt_main_group');
            $table->dropColumn('is_gantt_name');
            $table->dropColumn('is_gantt_parent');
            $table->dropColumn('is_gantt_start');
            $table->dropColumn('is_gantt_end');
            $table->dropColumn('is_gantt_progress');
            $table->dropColumn('is_gantt_color');
            $table->dropColumn('is_gantt_tooltip');
            $table->dropColumn('is_gantt_left_header');
            $table->dropColumn('is_gantt_label_symbol');
            $table->dropColumn('is_gantt_milestone');

            $table->dropColumn('is_calendar_start');
            $table->dropColumn('is_calendar_end');
            $table->dropColumn('is_calendar_title');
            $table->dropColumn('is_calendar_cond_format');
        });
    }
}
