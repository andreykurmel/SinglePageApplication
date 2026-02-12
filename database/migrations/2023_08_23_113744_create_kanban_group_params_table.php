<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanGroupParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_kanban_group_params', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_kanban_id');
            $table->unsignedInteger('table_field_id');
            $table->string('stat', 32);

            $table->foreign('table_kanban_id')
                ->references('id')
                ->on('table_kanban_settings')
                ->onDelete('cascade');

            $table->foreign('table_field_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::dropIfExists('table_kanban_rights');
    }
}
