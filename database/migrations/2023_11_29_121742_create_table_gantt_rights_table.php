<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGanttRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_gantt_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_gantt_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_gantt_id', 'tgr__table_gantt_id')
                ->references('id')
                ->on('table_gantts')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'tgr__table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::dropIfExists('table_gantt_rights');
    }
}
