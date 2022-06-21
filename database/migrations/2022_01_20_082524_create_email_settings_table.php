<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email_code', 128);
            $table->string('scenario', 255);
            $table->string('sender_name', 128)->nullable();
            $table->string('sender_email', 128)->nullable();
            $table->string('to', 255)->nullable();
            $table->string('cc', 255)->nullable();
            $table->string('bcc', 255)->nullable();
            $table->string('subject', 512)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_settings');
    }
}
