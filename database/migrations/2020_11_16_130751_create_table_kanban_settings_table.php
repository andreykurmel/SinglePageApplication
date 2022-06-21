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
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('kanban_group_field_id')->nullable();
            $table->text('columns_order')->nullable();
            $table->text('cards_order')->nullable();

            $table->foreign('table_field_id', 'table_kanban_settings__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('kanban_group_field_id', 'table_kanban_settings__kanban_group_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });

        Schema::create('table_kanban_settings_2_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_kanban_setting_id');
            $table->unsignedInteger('table_field_id');
            $table->tinyInteger('table_show_name')->default(1);
            $table->string('picture_style', 16)->default('scroll');
            $table->tinyInteger('cell_border')->default(1);
            $table->string('picture_fit', 16)->default('fill');

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
