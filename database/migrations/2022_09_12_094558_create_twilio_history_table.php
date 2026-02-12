<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('table_id')->nullable();
            $table->unsignedInteger('table_field_id')->nullable();
            $table->unsignedInteger('row_id')->nullable();
            $table->string('type', 32);
            $table->string('receiver', 255);
            $table->string('incoming_app_id', 255)->nullable();
            $table->json('content');
            $table->tinyInteger('missed')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');

            $table->index('row_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_history');
    }
}
