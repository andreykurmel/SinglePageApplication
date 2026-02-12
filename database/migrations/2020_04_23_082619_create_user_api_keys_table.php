<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 255)->default('');
            $table->string('type', 32)->default('google');
            $table->string('model', 32)->nullable();
            $table->string('key', 1024);
            $table->string('auth_token', 1024)->nullable();
            $table->string('air_base', 128)->nullable();
            $table->string('air_type', 128)->nullable();
            $table->string('twiml_app_id', 255)->nullable();
            $table->string('twilio_phone', 128)->nullable();
            $table->string('jira_email', 255)->nullable();
            $table->string('jira_host', 128)->nullable();
            $table->string('search_key', 255)->nullable();
            $table->string('notes', 255)->default('');
            $table->tinyInteger('is_active')->default(0);

            $table->foreign('user_id')
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
        Schema::dropIfExists('user_api_keys');
    }
}
