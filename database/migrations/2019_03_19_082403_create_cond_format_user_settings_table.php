<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCondFormatUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cond_format_user_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cond_format_id');
            $table->unsignedInteger('user_id');
            $table->tinyInteger('status')->default(1);

            $table->foreign('cond_format_id', 'cond_format_user_settings__cond_formats')
                ->references('id')
                ->on('cond_formats')
                ->onDelete('cascade');

            $table->foreign('user_id', 'cond_format_user_settings__users')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('cond_format_user_settings');
    }
}
