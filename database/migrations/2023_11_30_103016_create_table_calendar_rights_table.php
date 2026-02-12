<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCalendarRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_calendar_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_calendar_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_calendar_id', 'tcr__table_calendar_id')
                ->references('id')
                ->on('table_calendars')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'tcr__table_permission_id')
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
        Schema::dropIfExists('table_calendar_rights');
    }
}
