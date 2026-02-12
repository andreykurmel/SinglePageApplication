<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_tournaments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('description', 255)->nullable();
            $table->tinyInteger('tour_active')->default(1);
            $table->string('tb_tour_data_range', 16)->default('0');
            $table->unsignedInteger('teamhomename_fld_id')->nullable();
            $table->unsignedInteger('teamhomegoals_fld_id')->nullable();
            $table->unsignedInteger('teamguestname_fld_id')->nullable();
            $table->unsignedInteger('teamguestgoals_fld_id')->nullable();
            $table->unsignedInteger('stage_fld_id')->nullable();
            $table->unsignedInteger('date_fld_id')->nullable();
            $table->unsignedInteger('p_team_width')->default(70);
            $table->unsignedInteger('p_goal_width')->default(40);
            $table->unsignedInteger('p_match_margin')->default(10);
            $table->unsignedInteger('p_round_margin')->default(30);

            $table->foreign('table_id', 'table_tournaments__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('teamhomename_fld_id', 'table_tournaments__teamhomename_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('teamhomegoals_fld_id', 'table_tournaments__teamhomegoals_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('teamguestname_fld_id', 'table_tournaments__teamguestname_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('teamguestgoals_fld_id', 'table_tournaments__teamguestgoals_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('stage_fld_id', 'table_tournaments__stage_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('date_fld_id', 'table_tournaments__date_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_tournaments');
    }
}
