<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTournamentRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_tournament_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_tournament_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_tournament_id', 'ttor__table_tournament_id')
                ->references('id')
                ->on('table_tournaments')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'ttor__table_permission_id')
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
        Schema::dropIfExists('table_tournament_rights');
    }
}
