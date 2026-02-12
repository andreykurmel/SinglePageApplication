<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCalendarsAndTableGanttsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_gantts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('gantt_data_range', 32)->default('0');
            $table->string('gantt_info_type', 16)->nullable();
            $table->unsignedInteger('gantt_navigation')->nullable();
            $table->unsignedInteger('gantt_navigator_bottom')->nullable();
            $table->unsignedInteger('gantt_navigator_height')->default(40);
            $table->unsignedInteger('gantt_row_height')->default(24);
            $table->unsignedInteger('gantt_show_names')->nullable();
            $table->unsignedInteger('gantt_highlight')->nullable();
            $table->unsignedInteger('gantt_active')->default(1);
            $table->string('gantt_day_format', 16)->nullable();
            $table->string('description', 255)->nullable();

            $table->foreign('table_id', 'table_gantts__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });

        Schema::create('table_gantt_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_gantt_id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedTinyInteger('gantt_left_header')->nullable();
            $table->unsignedTinyInteger('gantt_tooltip')->nullable();

            $table->unsignedTinyInteger('is_gantt_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_parent_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_main_group')->nullable();
            $table->unsignedTinyInteger('is_gantt_name')->nullable();
            $table->unsignedTinyInteger('is_gantt_parent')->nullable();
            $table->unsignedTinyInteger('is_gantt_start')->nullable();
            $table->unsignedTinyInteger('is_gantt_end')->nullable();
            $table->unsignedTinyInteger('is_gantt_progress')->nullable();
            $table->unsignedTinyInteger('is_gantt_color')->nullable();
            $table->unsignedTinyInteger('is_gantt_label_symbol')->nullable();
            $table->unsignedTinyInteger('is_gantt_milestone')->nullable();

            $table->foreign('table_gantt_id', 'table_gantt_settings__gantt_id')
                ->references('id')
                ->on('table_gantts')
                ->onDelete('cascade');
            $table->foreign('table_field_id', 'table_gantt_settings__field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });

        Schema::create('table_calendars', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('calendar_data_range', 32)->default('0');
            $table->string('calendar_locale', 32)->nullable();
            $table->string('calendar_timezone', 32)->nullable();
            $table->string('calendar_init_date', 32)->default('present');
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('calendar_active')->default(1);

            $table->unsignedInteger('fldid_calendar_start')->nullable();
            $table->unsignedInteger('fldid_calendar_end')->nullable();
            $table->unsignedInteger('fldid_calendar_title')->nullable();
            $table->unsignedInteger('fldid_calendar_cond_format')->nullable();

            $table->foreign('fldid_calendar_start', 'table_calendars__fldid_calendar_start')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('fldid_calendar_end', 'table_calendars__fldid_calendar_end')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('fldid_calendar_title', 'table_calendars__fldid_calendar_title')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('fldid_calendar_cond_format', 'table_calendars__fldid_calendar_cond_format')->references('id')->on('table_fields')->onDelete('cascade');

            $table->foreign('table_id', 'table_calendars__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_gantts');
        Schema::dropIfExists('table_gantt_settings');
        Schema::dropIfExists('table_calendars');
    }
}
