<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_user_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->integer('initial_view_id')->default(-1);

            $table->foreign('table_id', 'table_user_settings__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('user_id', 'table_user_settings__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['table_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_user_settings');
    }
}
