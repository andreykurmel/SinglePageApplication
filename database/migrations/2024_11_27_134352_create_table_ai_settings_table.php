<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAiSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_ai_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_ai_id');
            $table->string('who', 32);
            $table->text('content');
            $table->timestamps();

            $table->foreign('table_ai_id', 'table_ais__table_ai_id')
                ->references('id')
                ->on('table_ais')
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
        Schema::dropIfExists('table_ai_messages');
    }
}
