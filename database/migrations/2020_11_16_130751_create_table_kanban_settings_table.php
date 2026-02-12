<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKanbanSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_kanban_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('kanban_group_field_id')->nullable();
            $table->text('columns_order')->nullable();
            $table->text('cards_order')->nullable();
            $table->tinyInteger('kanban_active')->default(1);

            $table->string('kanban_field_name', 128)->nullable();
            $table->string('kanban_field_description', 255)->nullable();
            $table->tinyInteger('kanban_form_table')->default(1);
            $table->tinyInteger('kanban_center_align')->nullable();
            $table->unsignedInteger('kanban_card_width')->default(300);
            $table->unsignedInteger('kanban_card_height')->nullable();
            $table->string('kanban_sort_type', 16)->nullable();
            $table->unsignedInteger('kanban_row_spacing')->default(0);
            $table->string('kanban_header_color', 16)->default('#DDD');
            $table->tinyInteger('kanban_hide_empty_tab')->default(1);
            $table->unsignedInteger('kanban_picture_field')->nullable();
            $table->string('kanban_picture_position', 16)->default('right');
            $table->float('kanban_picture_width')->default('30');
            $table->string('kanban_data_range', 32)->default('0');

            $table->foreign('table_id', 'table_kanban_settings__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_kanban_settings__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('kanban_group_field_id', 'table_kanban_settings__kanban_group_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('kanban_picture_field', 'table_kanban_settings__kanban_picture_field')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });

        Schema::create('table_kanban_settings_2_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_kanban_setting_id');
            $table->unsignedInteger('table_field_id');
            $table->tinyInteger('is_header_show')->default(0);
            $table->tinyInteger('is_header_value')->default(0);
            $table->tinyInteger('table_show_name')->default(1);
            $table->tinyInteger('table_show_value')->default(1);
            $table->string('picture_style', 16)->default('scroll');
            $table->tinyInteger('cell_border')->default(1);
            $table->string('picture_fit', 16)->default('fill');
            $table->string('width_of_table_popup', 16)->default('full');
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->tinyInteger('is_table_field_in_popup')->default(0);
            $table->tinyInteger('is_hdr_lvl_one_row')->default(0);

            $table->foreign('table_field_id', 'tks2tf__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('table_kanban_setting_id', 'tks2tf__table_kanban_setting_id')
                ->references('id')
                ->on('table_kanban_settings')
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
        Schema::dropIfExists('table_kanban_settings');
        Schema::dropIfExists('table_kanban_settings_2_table_fields');
    }
}
